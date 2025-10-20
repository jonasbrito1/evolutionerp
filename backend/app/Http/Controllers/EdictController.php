<?php

namespace App\Http\Controllers;

use App\Models\Edict;
use App\Models\KanbanCard;
use App\Models\KanbanColumn;
use App\Services\EdictAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EdictController extends Controller
{
    protected $analysisService;

    public function __construct(EdictAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * POST /api/edicts/upload
     * Upload e análise de edital (PDF, Word, Excel, etc)
     */
    public function upload(Request $request)
    {
        // Aumentar timeout para 5 minutos (300 segundos)
        set_time_limit(300);
        ini_set('max_execution_time', '300');

        $request->validate([
            'pdf' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv,txt|max:51200', // 50MB
            'company_id' => 'required|exists:companies,id',
        ]);

        try {
            // Salvar arquivo
            $file = $request->file('pdf');
            $path = $file->store('editais/uploads');
            $fullPath = Storage::path($path);

            Log::info('Upload de edital iniciado', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'path' => $path
            ]);

            // Criar registro inicial com campos obrigatórios temporários
            $edict = Edict::create([
                'company_id' => $request->company_id,
                'file_path' => $path,
                'status' => 'draft',
                'processing_status' => 'pending',
                'edict_number' => 'TEMP-' . time(),
                'organ' => 'Processando...', // Campo obrigatório - será atualizado após análise
                'description' => 'Processando análise do edital...', // Campo obrigatório - será atualizado após análise
            ]);

            // Processar análise
            try {
                $edict->update(['processing_status' => 'processing']);

                $analysis = $this->analysisService->analyzeEdict($edict, $fullPath);

                // Sanitizar dados para UTF-8 válido
                $analysis = $this->sanitizeUtf8($analysis);

                // Retornar dados para revisão SEM salvar ainda
                return response()->json([
                    'success' => true,
                    'message' => 'Análise concluída! Revise os dados antes de salvar.',
                    'edict_id' => $edict->id,
                    'file_path' => $path,
                    'analysis' => $analysis,
                    'requires_review' => true,
                ], 200);

            } catch (\Exception $e) {
                Log::error('Erro ao analisar edital: ' . $e->getMessage(), [
                    'edict_id' => $edict->id,
                    'file_path' => $fullPath,
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);

                $edict->update([
                    'processing_status' => 'failed',
                    'processing_error' => $e->getMessage(),
                ]);

                // Mensagens de erro mais amigáveis
                $userMessage = 'Erro ao analisar o edital';
                if (strpos($e->getMessage(), 'execution time') !== false) {
                    $userMessage = 'O arquivo é muito grande e excedeu o tempo de processamento. Tente um arquivo menor ou contate o suporte.';
                } elseif (strpos($e->getMessage(), 'memory') !== false) {
                    $userMessage = 'O arquivo é muito grande para processar. Tente um arquivo menor.';
                } elseif (strpos($e->getMessage(), 'API') !== false || strpos($e->getMessage(), 'Claude') !== false) {
                    $userMessage = 'Erro na análise com IA. Verifique a configuração da API Claude ou tente novamente.';
                } elseif (strpos($e->getMessage(), 'PDF') !== false) {
                    $userMessage = 'Erro ao processar o PDF. Verifique se o arquivo não está corrompido.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $userMessage,
                    'error' => $e->getMessage(),
                    'edict_id' => $edict->id,
                    'can_retry' => true,
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Erro no upload do edital: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload do edital',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/edicts
     * Listar editais com filtros (OTIMIZADO)
     */
    public function index(Request $request)
    {
        // Eager loading otimizado - carrega apenas campos necessários
        $query = Edict::select([
            'id', 'company_id', 'edict_number', 'organ', 'description',
            'status', 'estimated_value', 'closing_date', 'opening_date', 'session_date',
            'publication_date', 'category', 'modality', 'worth_participating',
            'processing_status', 'ai_score', 'created_at'
        ])->with(['company:id,name']); // Carrega apenas campos necessários da empresa

        // Filtros
        if ($request->has('worth_participating')) {
            $query->where('worth_participating', $request->worth_participating);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('processing_status')) {
            $query->where('processing_status', $request->processing_status);
        }

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('modality')) {
            $query->where('modality', $request->modality);
        }

        // Busca por texto otimizada com índices
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('edict_number', 'like', "%{$search}%")
                  ->orWhere('organ', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('object_description', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginação
        $perPage = $request->get('per_page', 20);
        $edicts = $query->paginate($perPage);

        return response()->json($edicts);
    }

    /**
     * GET /api/edicts/{id}
     * Detalhes completos do edital
     */
    public function show($id)
    {
        try {
            $edict = Edict::with(['company', 'analysis'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'edict' => $edict,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Edital não encontrado',
            ], 404);
        }
    }

    /**
     * PUT /api/edicts/{id}
     * Atualizar edital
     */
    public function update(Request $request, $id)
    {
        try {
            $edict = Edict::findOrFail($id);

            // Log para debug
            Log::info('Atualizando edital', [
                'edict_id' => $id,
                'data_keys' => array_keys($request->all())
            ]);

            // Validar se o edict_number já existe em outro registro
            if ($request->has('edict_number') && $request->edict_number) {
                $existingEdict = Edict::where('edict_number', $request->edict_number)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingEdict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Já existe um edital cadastrado com este número: ' . $request->edict_number,
                        'error' => 'duplicate_edict_number',
                        'existing_edict_id' => $existingEdict->id,
                    ], 422);
                }
            }

            // Atualizar apenas campos permitidos (fillable)
            $edict->update($request->all());

            // Atualizar status de processamento se foi salvo com sucesso
            if ($edict->processing_status === 'processing' || $edict->processing_status === 'pending') {
                $edict->update([
                    'processing_status' => 'completed',
                    'processed_at' => now(),
                ]);
            }

            // SEMPRE adicionar ao Kanban quando edital for marcado para participar
            if ($request->worth_participating == true || $request->worth_participating == 1) {
                $this->addEdictToKanban($edict, $request->user());
            }

            return response()->json([
                'success' => true,
                'message' => 'Edital atualizado com sucesso',
                'edict' => $edict->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar edital', [
                'edict_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar edital',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/edicts/{id}
     * Remover edital
     */
    public function destroy($id)
    {
        try {
            $edict = Edict::findOrFail($id);

            // Remover arquivo
            if ($edict->file_path) {
                Storage::delete($edict->file_path);
            }

            $edict->delete();

            return response()->json([
                'success' => true,
                'message' => 'Edital removido com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover edital',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/edicts/{id}/reanalyze
     * Reprocessar análise do edital
     */
    public function reanalyze($id)
    {
        // Aumentar timeout para 5 minutos (300 segundos)
        set_time_limit(300);
        ini_set('max_execution_time', '300');

        try {
            $edict = Edict::findOrFail($id);

            if (!$edict->file_path || !Storage::exists($edict->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado',
                ], 404);
            }

            $fullPath = Storage::path($edict->file_path);

            $edict->update(['processing_status' => 'processing']);

            $analysis = $this->analysisService->analyzeEdict($edict, $fullPath);

            $edict->update([
                ...$analysis,
                'processing_status' => 'completed',
                'processed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Edital reprocessado com sucesso',
                'edict' => $edict->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao reprocessar edital: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao reprocessar edital',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/edicts/{id}/download
     * Download do arquivo do edital
     */
    public function download($id)
    {
        try {
            $edict = Edict::findOrFail($id);

            if (!$edict->file_path || !Storage::exists($edict->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado',
                ], 404);
            }

            // Detectar extensão do arquivo
            $extension = pathinfo($edict->file_path, PATHINFO_EXTENSION);
            $filename = 'Edital_' . $edict->edict_number . '.' . $extension;

            return Storage::download($edict->file_path, $filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar arquivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/edicts/stats
     * Estatísticas dos editais (OTIMIZADO COM CACHE)
     */
    public function stats(Request $request)
    {
        $companyId = $request->get('company_id', 'all');
        $cacheKey = "edict_stats_{$companyId}";

        // Cache por 5 minutos
        $stats = Cache::remember($cacheKey, 300, function () use ($companyId) {
            $query = Edict::query();

            if ($companyId !== 'all') {
                $query->where('company_id', $companyId);
            }

            // Usar uma única query com subqueries para melhor performance
            return [
                'total' => $query->count(),
                'recommended' => (clone $query)->where('worth_participating', true)->count(),
                'not_recommended' => (clone $query)->where('worth_participating', false)->count(),
                'processing' => (clone $query)->where('processing_status', 'processing')->count(),
                'failed' => (clone $query)->where('processing_status', 'failed')->count(),
                'by_status' => (clone $query)->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_modality' => (clone $query)->selectRaw('modality, COUNT(*) as count')
                    ->groupBy('modality')
                    ->whereNotNull('modality')
                    ->pluck('count', 'modality'),
                'total_estimated_value' => (clone $query)->sum('estimated_value') ?? 0,
                'average_ai_score' => (clone $query)->avg('ai_score') ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Inicializar Kanban para uma empresa
     */
    protected function initializeKanbanForCompany($companyId)
    {
        $defaultColumns = [
            ['name' => '📋 Análise Edital', 'color' => '#3B82F6', 'order' => 1],
            ['name' => '💰 Orçamento', 'color' => '#F59E0B', 'order' => 2],
            ['name' => '📝 Cadastro Portal', 'color' => '#8B5CF6', 'order' => 3],
            ['name' => '📄 Envio Documentação', 'color' => '#EC4899', 'order' => 4],
            ['name' => '⏰ Aguardando Abertura', 'color' => '#6366F1', 'order' => 5],
            ['name' => '⚔️ Disputa/Lances', 'color' => '#EF4444', 'order' => 6],
            ['name' => '✅ Habilitação', 'color' => '#10B981', 'order' => 7],
            ['name' => '🏆 Vencemos', 'color' => '#059669', 'order' => 8, 'is_final' => true],
            ['name' => '❌ Perdemos', 'color' => '#DC2626', 'order' => 9, 'is_final' => true],
        ];

        foreach ($defaultColumns as $columnData) {
            KanbanColumn::create([
                'company_id' => $companyId,
                'name' => $columnData['name'],
                'color' => $columnData['color'],
                'order' => $columnData['order'],
                'is_final' => $columnData['is_final'] ?? false,
            ]);
        }

        Log::info('Kanban inicializado automaticamente', ['company_id' => $companyId]);
    }

    /**
     * Adicionar edital ao Kanban automaticamente
     */
    protected function addEdictToKanban($edict, $user)
    {
        try {
            // Verificar se já existe um card para este edital
            $existingCard = KanbanCard::where('edict_id', $edict->id)->first();
            if ($existingCard) {
                return; // Já existe, não criar duplicado
            }

            // Verificar se Kanban está inicializado
            $hasKanban = KanbanColumn::where('company_id', $user->company_id)->exists();

            if (!$hasKanban) {
                // Inicializar Kanban automaticamente
                $this->initializeKanbanForCompany($user->company_id);
            }

            // Pegar a coluna "Orçamento" (segunda coluna - já passou pela análise)
            $orcamentoColumn = KanbanColumn::where('company_id', $user->company_id)
                                           ->orderBy('order')
                                           ->skip(1) // Pula "Análise Edital", vai direto pra "Orçamento"
                                           ->first();

            if (!$orcamentoColumn) {
                // Se não existe, pega a primeira coluna disponível
                $orcamentoColumn = KanbanColumn::where('company_id', $user->company_id)
                                               ->orderBy('order')
                                               ->first();
            }

            if (!$orcamentoColumn) {
                Log::warning('Não foi possível criar card no Kanban: nenhuma coluna disponível', [
                    'company_id' => $user->company_id
                ]);
                return; // Kanban não inicializado
            }

            // Determinar prioridade baseada no prazo
            $priority = 'medium';
            if ($edict->session_date) {
                $daysUntilSession = now()->diffInDays($edict->session_date, false);
                if ($daysUntilSession <= 3) {
                    $priority = 'urgent';
                } elseif ($daysUntilSession <= 7) {
                    $priority = 'high';
                }
            }

            // Preparar título (limitar a 255 caracteres)
            $title = $edict->object_description ?? $edict->description ?? "Edital {$edict->edict_number}";
            if (strlen($title) > 250) {
                $title = substr($title, 0, 247) . '...';
            }

            // Criar card no Kanban
            KanbanCard::create([
                'company_id' => $user->company_id,
                'kanban_column_id' => $orcamentoColumn->id,
                'edict_id' => $edict->id,
                'title' => $title,
                'description' => "Edital {$edict->edict_number} - {$edict->organ}",
                'edict_number' => $edict->edict_number,
                'estimated_value' => $edict->estimated_value,
                'deadline' => $edict->proposal_deadline,
                'session_date' => $edict->session_date,
                'priority' => $priority,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
                'order' => KanbanCard::where('kanban_column_id', $orcamentoColumn->id)->max('order') + 1,
            ]);

            Log::info('Edital adicionado ao Kanban automaticamente', [
                'edict_id' => $edict->id,
                'edict_number' => $edict->edict_number
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao adicionar edital ao Kanban', [
                'edict_id' => $edict->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sanitiza dados para UTF-8 válido
     */
    private function sanitizeUtf8($data)
    {
        if (is_string($data)) {
            // Remove caracteres inválidos e converte para UTF-8
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeUtf8($value);
            }
        }

        return $data;
    }
}
