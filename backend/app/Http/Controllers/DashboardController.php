<?php

namespace App\Http\Controllers;

use App\Models\Edict;
use App\Models\CompanyDocument;
use App\Models\FinancialTransaction;
use App\Models\KanbanCard;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->input('company_id', 1);

        \Log::info('Dashboard request', ['company_id' => $companyId]);

        // ===== EDITAIS =====
        $totalEdicts = Edict::where('company_id', $companyId)->count();
        $edictsThisMonth = Edict::where('company_id', $companyId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        $edictsByStatus = Edict::where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // ===== DOCUMENTOS =====
        $totalDocuments = CompanyDocument::where('company_id', $companyId)->count();
        $documentsExpiring = CompanyDocument::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->count();
        $documentsByType = CompanyDocument::where('company_id', $companyId)
            ->select('document_type', DB::raw('count(*) as total'))
            ->groupBy('document_type')
            ->get();

        // ===== FINANCEIRO =====
        $financialStats = [
            'total_receitas' => FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'receita')
                ->whereIn('status', ['recebido', 'pago'])
                ->sum('amount'),
            'total_despesas' => FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'despesa')
                ->whereIn('status', ['pago', 'recebido'])
                ->sum('amount'),
            'receitas_pendentes' => FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'receita')
                ->where('status', 'pendente')
                ->sum('amount'),
            'despesas_pendentes' => FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'despesa')
                ->where('status', 'pendente')
                ->sum('amount'),
            'contas_atrasadas' => FinancialTransaction::where('company_id', $companyId)
                ->where('status', 'pendente')
                ->where('due_date', '<', Carbon::now())
                ->count(),
        ];
        $financialStats['saldo'] = $financialStats['total_receitas'] - $financialStats['total_despesas'];

        // Evolução financeira últimos 6 meses
        $financialEvolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $receitas = FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'receita')
                ->whereYear('due_date', $month->year)
                ->whereMonth('due_date', $month->month)
                ->sum('amount');
            $despesas = FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'despesa')
                ->whereYear('due_date', $month->year)
                ->whereMonth('due_date', $month->month)
                ->sum('amount');

            $financialEvolution[] = [
                'month' => $month->format('M/Y'),
                'receitas' => $receitas,
                'despesas' => $despesas,
                'saldo' => $receitas - $despesas
            ];
        }

        // Despesas por categoria
        $expensesByCategory = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'despesa')
            ->select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Receitas por categoria
        $incomeByCategory = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'receita')
            ->select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // ===== KANBAN =====
        $totalCards = KanbanCard::where('company_id', $companyId)->count();
        $cardsByColumn = KanbanCard::where('kanban_cards.company_id', $companyId)
            ->join('kanban_columns', 'kanban_cards.kanban_column_id', '=', 'kanban_columns.id')
            ->select('kanban_columns.name as column', DB::raw('count(*) as total'))
            ->groupBy('kanban_columns.name', 'kanban_columns.id')
            ->get();

        // ===== ATIVIDADES RECENTES =====
        $recentEdicts = Edict::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'edict_number', 'organ', 'status', 'created_at']);

        $recentTransactions = FinancialTransaction::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'description', 'amount', 'type', 'status', 'created_at']);

        \Log::info('Dashboard data summary', [
            'editais_count' => $totalEdicts,
            'documentos_count' => $totalDocuments,
            'receitas' => $financialStats['total_receitas'],
            'despesas' => $financialStats['total_despesas'],
            'kanban_cards' => $totalCards,
            'recent_edicts_count' => $recentEdicts->count(),
            'recent_transactions_count' => $recentTransactions->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'editais' => [
                    'total' => $totalEdicts,
                    'this_month' => $edictsThisMonth,
                    'by_status' => $edictsByStatus,
                    'recent' => $recentEdicts
                ],
                'documentos' => [
                    'total' => $totalDocuments,
                    'expiring_soon' => $documentsExpiring,
                    'by_type' => $documentsByType
                ],
                'financeiro' => [
                    'stats' => $financialStats,
                    'evolution' => $financialEvolution,
                    'expenses_by_category' => $expensesByCategory,
                    'income_by_category' => $incomeByCategory,
                    'recent_transactions' => $recentTransactions
                ],
                'kanban' => [
                    'total_cards' => $totalCards,
                    'by_column' => $cardsByColumn
                ]
            ]
        ]);
    }
}
