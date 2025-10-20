<?php

namespace App\Http\Controllers;

use App\Models\KanbanColumn;
use App\Models\KanbanCard;
use App\Models\KanbanComment;
use App\Models\KanbanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KanbanController extends Controller
{
    /**
     * GET /api/kanban
     * Retornar todo o board do Kanban (colunas + cards) - OTIMIZADO
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $cacheKey = "kanban_board_{$companyId}";

        // Cache por 2 minutos (kanban é mais dinâmico)
        $columns = Cache::remember($cacheKey, 120, function () use ($companyId) {
            return KanbanColumn::where('company_id', $companyId)
                ->with([
                    'cards' => function ($query) {
                        // Carregar apenas campos necessários
                        $query->select([
                            'id', 'company_id', 'kanban_column_id', 'edict_id',
                            'title', 'description', 'edict_number', 'estimated_value',
                            'deadline', 'session_date', 'priority', 'order',
                            'has_budget', 'has_suppliers', 'has_certificates',
                            'has_documents', 'team_approved', 'assigned_to', 'created_by'
                        ])
                        ->with([
                            'assignedUser:id,name',
                            'createdByUser:id,name',
                            'edict:id,edict_number,organ,status'
                        ])
                        ->withCount('comments')
                        ->orderBy('order');
                    }
                ])
                ->orderBy('order')
                ->get();
        });

        return response()->json([
            'success' => true,
            'columns' => $columns,
        ]);
    }

    /**
     * POST /api/kanban/columns
     * Criar nova coluna
     */
    public function createColumn(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'order' => 'integer',
        ]);

        $column = KanbanColumn::create([
            'company_id' => $request->user()->company_id,
            'name' => $request->name,
            'color' => $request->color,
            'order' => $request->order ?? KanbanColumn::where('company_id', $request->user()->company_id)->max('order') + 1,
            'is_final' => $request->is_final ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coluna criada com sucesso',
            'column' => $column,
        ], 201);
    }

    /**
     * PUT /api/kanban/columns/{id}
     * Atualizar coluna
     */
    public function updateColumn(Request $request, $id)
    {
        $column = KanbanColumn::where('company_id', $request->user()->company_id)
                              ->findOrFail($id);

        $column->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Coluna atualizada',
            'column' => $column->fresh(),
        ]);
    }

    /**
     * DELETE /api/kanban/columns/{id}
     * Deletar coluna (apenas se vazia)
     */
    public function deleteColumn(Request $request, $id)
    {
        $column = KanbanColumn::where('company_id', $request->user()->company_id)
                              ->findOrFail($id);

        if ($column->cards()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar coluna com cards',
            ], 400);
        }

        $column->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coluna deletada',
        ]);
    }

    /**
     * POST /api/kanban/cards
     * Criar novo card
     */
    public function createCard(Request $request)
    {
        $request->validate([
            'kanban_column_id' => 'required|exists:kanban_columns,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'edict_number' => 'nullable|string',
            'estimated_value' => 'nullable|numeric',
            'deadline' => 'nullable|date',
            'session_date' => 'nullable|date',
            'priority' => 'in:low,medium,high,urgent',
        ]);

        $card = KanbanCard::create([
            'company_id' => $request->user()->company_id,
            'kanban_column_id' => $request->kanban_column_id,
            'title' => $request->title,
            'description' => $request->description,
            'edict_number' => $request->edict_number,
            'estimated_value' => $request->estimated_value,
            'deadline' => $request->deadline,
            'session_date' => $request->session_date,
            'priority' => $request->priority ?? 'medium',
            'created_by' => $request->user()->id,
            'assigned_to' => $request->assigned_to ?? $request->user()->id,
            'order' => KanbanCard::where('kanban_column_id', $request->kanban_column_id)->max('order') + 1,
        ]);

        // Registrar no histórico
        KanbanHistory::create([
            'kanban_card_id' => $card->id,
            'user_id' => $request->user()->id,
            'from_column_id' => null,
            'to_column_id' => $request->kanban_column_id,
            'note' => 'Card criado',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Oportunidade adicionada ao Kanban!',
            'card' => $card->load(['assignedUser', 'createdByUser', 'column']),
        ], 201);
    }

    /**
     * GET /api/kanban/cards/{id}
     * Detalhes do card
     */
    public function showCard(Request $request, $id)
    {
        $card = KanbanCard::where('company_id', $request->user()->company_id)
                          ->with([
                              'column',
                              'edict',
                              'assignedUser',
                              'createdByUser',
                              'comments.user',
                              'history.user',
                              'history.fromColumn',
                              'history.toColumn'
                          ])
                          ->findOrFail($id);

        return response()->json([
            'success' => true,
            'card' => $card,
        ]);
    }

    /**
     * PUT /api/kanban/cards/{id}
     * Atualizar card
     */
    public function updateCard(Request $request, $id)
    {
        $card = KanbanCard::where('company_id', $request->user()->company_id)
                          ->findOrFail($id);

        $card->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Card atualizado',
            'card' => $card->fresh(['assignedUser', 'createdByUser', 'column']),
        ]);
    }

    /**
     * POST /api/kanban/cards/{id}/move
     * Mover card para outra coluna
     */
    public function moveCard(Request $request, $id)
    {
        $request->validate([
            'kanban_column_id' => 'required|exists:kanban_columns,id',
            'order' => 'integer',
        ]);

        $card = KanbanCard::where('company_id', $request->user()->company_id)
                          ->findOrFail($id);

        $oldColumnId = $card->kanban_column_id;

        // Atualizar coluna e ordem
        $card->update([
            'kanban_column_id' => $request->kanban_column_id,
            'order' => $request->order ?? KanbanCard::where('kanban_column_id', $request->kanban_column_id)->max('order') + 1,
        ]);

        // Registrar movimentação no histórico
        KanbanHistory::create([
            'kanban_card_id' => $card->id,
            'user_id' => $request->user()->id,
            'from_column_id' => $oldColumnId,
            'to_column_id' => $request->kanban_column_id,
            'note' => $request->note ?? null,
        ]);

        // Limpar cache do kanban
        Cache::forget("kanban_board_{$request->user()->company_id}");

        return response()->json([
            'success' => true,
            'message' => 'Card movido com sucesso',
            'card' => $card->fresh(['column']),
        ]);
    }

    /**
     * POST /api/kanban/cards/{id}/checklist
     * Atualizar itens do checklist
     */
    public function updateChecklist(Request $request, $id)
    {
        $card = KanbanCard::where('company_id', $request->user()->company_id)
                          ->findOrFail($id);

        $card->update([
            'has_budget' => $request->has_budget ?? $card->has_budget,
            'has_suppliers' => $request->has_suppliers ?? $card->has_suppliers,
            'has_certificates' => $request->has_certificates ?? $card->has_certificates,
            'has_documents' => $request->has_documents ?? $card->has_documents,
            'team_approved' => $request->team_approved ?? $card->team_approved,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checklist atualizado',
            'card' => $card->fresh(),
            'completion_percentage' => $card->completion_percentage,
        ]);
    }

    /**
     * DELETE /api/kanban/cards/{id}
     * Deletar card
     */
    public function deleteCard(Request $request, $id)
    {
        $card = KanbanCard::where('company_id', $request->user()->company_id)
                          ->findOrFail($id);

        $card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Card removido',
        ]);
    }

    /**
     * POST /api/kanban/cards/{id}/comments
     * Adicionar comentário ao card
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $card = KanbanCard::where('company_id', $request->user()->company_id)
                          ->findOrFail($id);

        $comment = KanbanComment::create([
            'kanban_card_id' => $card->id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentário adicionado',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * POST /api/kanban/initialize
     * Inicializar colunas padrão do Kanban para a empresa
     */
    public function initialize(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Verificar se já tem colunas
        if (KanbanColumn::where('company_id', $companyId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kanban já inicializado',
            ], 400);
        }

        // Criar colunas padrão - ETAPAS DA LICITAÇÃO
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
                ...$columnData
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kanban inicializado com sucesso!',
            'columns' => KanbanColumn::where('company_id', $companyId)->orderBy('order')->get(),
        ]);
    }
}
