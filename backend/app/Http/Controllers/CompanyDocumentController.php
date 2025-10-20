<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompanyDocumentController extends Controller
{
    /**
     * GET /api/company/documents
     * Listar documentos da empresa
     */
    public function index(Request $request)
    {
        try {
            $query = CompanyDocument::query();

            // Filtro por empresa (default company_id = 1)
            $companyId = $request->get('company_id', 1);
            $query->where('company_id', $companyId);

            // Filtro por tipo de documento
            if ($request->has('type')) {
                $query->where('document_type', $request->type);
            }

            // Filtro por status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filtro de documentos expirando
            if ($request->has('expiring_days')) {
                $query->expiringSoon($request->expiring_days);
            }

            // Filtro de documentos expirados
            if ($request->boolean('expired')) {
                $query->where('expiry_date', '<', now());
            }

            // Busca por texto
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('document_name', 'like', "%{$search}%")
                      ->orWhere('document_type', 'like', "%{$search}%")
                      ->orWhere('reference_number', 'like', "%{$search}%");
                });
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Get all documents
            $documents = $query->get();

            // Update status for each document based on expiry date
            $documents->each(function ($doc) {
                $newStatus = $this->calculateDocumentStatus($doc->expiry_date);
                if ($doc->status !== $newStatus) {
                    $doc->status = $newStatus;
                    $doc->save();
                }
            });

            return response()->json([
                'success' => true,
                'data' => $documents,
                'total' => $documents->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar documentos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar documentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/company/documents/upload
     * Upload de documento da empresa
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'sometimes|file|max:10240', // 10MB - accepts 'file' or 'document'
            'document' => 'sometimes|file|max:10240', // 10MB
            'company_id' => 'required|integer',
            'document_type' => 'required|string',
            'document_name' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'issue_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'custom_category' => 'nullable|string', // Categoria customizada
        ]);

        try {
            // Accept both 'file' and 'document' field names
            $file = $request->file('file') ?? $request->file('document');

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum arquivo foi enviado',
                ], 422);
            }

            // Determinar categoria do documento
            // Se houver custom_category, usar ela; senão, tentar determinar pela tabela
            $category = $request->custom_category
                ? $request->custom_category
                : CompanyDocument::getCategoryForType($request->document_type);

            // Criar path organizado por empresa e categoria
            $path = $file->store(
                "docs/empresa/{$category}",
                'public'
            );

            // Calculate status based on expiry date
            $status = $this->calculateDocumentStatus($request->expiry_date);

            $document = CompanyDocument::create([
                'company_id' => $request->company_id,
                'document_type' => $request->document_type,
                'category' => $category,
                'document_name' => $request->document_name,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'reference_number' => $request->reference_number,
                'expiry_date' => $request->expiry_date,
                'issue_date' => $request->issue_date,
                'uploaded_by' => $request->user()->id ?? null,
                'status' => $status,
                'description' => $request->description,
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'uploaded_at' => now()->toIso8601String(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento enviado com sucesso!',
                'document' => $document,
                'data' => $document,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro no upload do documento: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload do documento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate document status based on expiry date
     */
    private function calculateDocumentStatus($expiryDate)
    {
        if (!$expiryDate) {
            return 'valid';
        }

        $expiry = Carbon::parse($expiryDate);
        $now = Carbon::now();
        $daysUntilExpiry = $now->diffInDays($expiry, false);

        if ($daysUntilExpiry < 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 30) {
            return 'expiring_soon';
        } else {
            return 'valid';
        }
    }

    /**
     * GET /api/company/documents/{id}
     * Detalhes do documento
     */
    public function show($id)
    {
        try {
            $document = CompanyDocument::with(['company', 'uploader'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'document' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Documento não encontrado',
            ], 404);
        }
    }

    /**
     * PUT /api/company/documents/{id}
     * Atualizar documento
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'file' => 'sometimes|file|max:10240',
            'document' => 'sometimes|file|max:10240',
            'document_type' => 'sometimes|string',
            'document_name' => 'sometimes|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'issue_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'custom_category' => 'nullable|string', // Categoria customizada
        ]);

        try {
            $document = CompanyDocument::findOrFail($id);

            // Atualizar campos básicos
            $updateData = [];

            if ($request->has('document_type')) {
                $updateData['document_type'] = $request->document_type;

                // Atualizar categoria também quando mudar o tipo
                $category = $request->custom_category
                    ? $request->custom_category
                    : CompanyDocument::getCategoryForType($request->document_type);
                $updateData['category'] = $category;
            }
            if ($request->has('document_name')) {
                $updateData['document_name'] = $request->document_name;
            }
            if ($request->has('reference_number')) {
                $updateData['reference_number'] = $request->reference_number;
            }
            if ($request->has('expiry_date')) {
                $updateData['expiry_date'] = $request->expiry_date;
                // Recalcular status baseado na nova data
                $updateData['status'] = $this->calculateDocumentStatus($request->expiry_date);
            }
            if ($request->has('issue_date')) {
                $updateData['issue_date'] = $request->issue_date;
            }
            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }

            // Se enviou novo arquivo, substituir
            $file = $request->file('file') ?? $request->file('document');
            if ($file) {
                // Deletar arquivo antigo
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                // Determinar categoria do documento
                $docType = $request->document_type ?? $document->document_type;
                // Se houver custom_category, usar ela; senão, tentar determinar pela tabela
                $category = $request->custom_category
                    ? $request->custom_category
                    : CompanyDocument::getCategoryForType($docType);

                // Upload novo arquivo
                $path = $file->store("docs/empresa/{$category}", 'public');

                $updateData['file_path'] = $path;
                $updateData['file_name'] = $file->getClientOriginalName();
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();

                // Atualizar metadata
                $metadata = $document->metadata ?? [];
                $metadata['original_name'] = $file->getClientOriginalName();
                $metadata['extension'] = $file->getClientOriginalExtension();
                $metadata['updated_at'] = now()->toIso8601String();
                $updateData['metadata'] = $metadata;
            }

            // Atualizar documento
            $document->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Documento atualizado com sucesso',
                'document' => $document->fresh(),
                'data' => $document->fresh(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Documento não encontrado',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar documento: ' . $e->getMessage(), [
                'document_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar documento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/company/documents/{id}
     * Remover documento
     */
    public function destroy($id)
    {
        try {
            $document = CompanyDocument::findOrFail($id);

            // Remover arquivo físico
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Soft delete
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento removido com sucesso',
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao remover documento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover documento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/company/documents/{id}/download
     * Download do documento
     */
    public function download($id)
    {
        try {
            $document = CompanyDocument::findOrFail($id);

            if (!$document->file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Caminho do arquivo não definido',
                ], 404);
            }

            // Verificar se arquivo existe no disco 'public'
            if (!Storage::disk('public')->exists($document->file_path)) {
                Log::error("Arquivo não encontrado: {$document->file_path}");
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no servidor',
                ], 404);
            }

            // Determinar nome do arquivo
            $fileName = $document->file_name ?? ($document->document_name . '.' . ($document->metadata['extension'] ?? 'pdf'));

            // Fazer download do arquivo
            return Storage::disk('public')->download($document->file_path, $fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Documento não encontrado',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Erro ao baixar documento: ' . $e->getMessage(), [
                'document_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar arquivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/company/documents/expiring
     * Listar documentos expirando em breve
     */
    public function expiring(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $companyId = $request->get('company_id');

            $query = CompanyDocument::expiringSoon($days)
                ->with(['company', 'uploader'])
                ->orderBy('expiry_date', 'asc');

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            $documents = $query->get();

            return response()->json([
                'success' => true,
                'count' => $documents->count(),
                'days' => $days,
                'documents' => $documents,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar documentos expirando: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar documentos expirando',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/company/documents/types
     * Listar tipos de documentos disponíveis
     */
    public function types()
    {
        try {
            $categories = CompanyDocument::getDocumentCategories();
            $types = CompanyDocument::getDocumentTypes();
            $typesFlat = CompanyDocument::getAllDocumentTypesFlat();

            return response()->json([
                'success' => true,
                'categories' => $categories,
                'types' => $types,
                'typesFlat' => $typesFlat,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de documentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/company/documents/stats
     * Estatísticas dos documentos
     */
    public function stats(Request $request)
    {
        try {
            $companyId = $request->get('company_id');

            $query = CompanyDocument::query();

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            $stats = [
                'total' => $query->count(),
                'valid' => (clone $query)->where('status', 'valid')->count(),
                'expired' => (clone $query)->where('status', 'expired')->count(),
                'pending_renewal' => (clone $query)->where('status', 'pending_renewal')->count(),
                'expiring_30_days' => CompanyDocument::expiringSoon(30)
                    ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                    ->count(),
                'expiring_60_days' => CompanyDocument::expiringSoon(60)
                    ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                    ->count(),
                'by_type' => (clone $query)->selectRaw('document_type, COUNT(*) as count')
                    ->groupBy('document_type')
                    ->pluck('count', 'document_type'),
                'by_status' => (clone $query)->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar estatísticas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar estatísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
