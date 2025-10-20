<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialController extends Controller
{
    // Listar transações
    public function index(Request $request)
    {
        $query = FinancialTransaction::with(['edict', 'creator'])
            ->where('company_id', $request->input('company_id', 1));

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Busca por texto (descrição, document_number, notes)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('document_number', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->orderBy('due_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    // Criar transação
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'company_id' => 'required|integer',
                'type' => 'required|in:receita,despesa',
                'category' => 'required|string',
                'description' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
                'payment_date' => 'nullable|date',
                'status' => 'nullable|in:pendente,pago,recebido,atrasado,cancelado',
                'payment_method' => 'nullable|string',
                'document_number' => 'nullable|string',
                'related_edict_id' => 'nullable|exists:edicts,id',
                'notes' => 'nullable|string',
            ]);

            $validated['created_by'] = $request->user()->id ?? null;

            // Garantir que status tenha valor default
            if (!isset($validated['status'])) {
                $validated['status'] = 'pendente';
            }

            $transaction = FinancialTransaction::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transação criada com sucesso',
                'data' => $transaction,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar transação financeira: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar transação: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Atualizar transação
    public function update(Request $request, $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|in:receita,despesa',
            'category' => 'sometimes|string',
            'description' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'payment_date' => 'nullable|date',
            'status' => 'sometimes|in:pendente,pago,recebido,atrasado,cancelado',
            'payment_method' => 'nullable|string',
            'document_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Transação atualizada com sucesso',
            'data' => $transaction->fresh(),
        ]);
    }

    // Deletar transação
    public function destroy($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transação excluída com sucesso',
        ]);
    }

    // Dashboard financeiro
    public function dashboard(Request $request)
    {
        $companyId = $request->input('company_id', 1);

        $stats = [
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

        $stats['saldo'] = $stats['total_receitas'] - $stats['total_despesas'];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Categorias
    public function categories()
    {
        $categories = FinancialCategory::where('active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // Métodos auxiliares
    public function paymentMethods()
    {
        return response()->json([
            'success' => true,
            'data' => FinancialTransaction::getPaymentMethods(),
        ]);
    }

    // Exportar para CSV
    public function exportCsv(Request $request)
    {
        try {
            \Log::info('Exportação CSV iniciada', [
                'user_id' => $request->user() ? $request->user()->id : 'não autenticado',
                'company_id' => $request->input('company_id', 1)
            ]);

            // Usar a mesma lógica de filtros do index
            $query = FinancialTransaction::with(['edict', 'creator'])
                ->where('company_id', $request->input('company_id', 1));

            // Aplicar filtros
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('description', 'LIKE', "%{$search}%")
                      ->orWhere('document_number', 'LIKE', "%{$search}%")
                      ->orWhere('notes', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
            }

            $transactions = $query->orderBy('due_date', 'desc')->get();

            // Criar arquivo CSV
            $filename = 'transacoes_financeiras_' . date('Y-m-d_His') . '.csv';
            $handle = fopen('php://temp', 'r+');

            // Adicionar BOM UTF-8 para Excel reconhecer corretamente os caracteres especiais
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeçalhos
            fputcsv($handle, [
                'ID',
                'Tipo',
                'Categoria',
                'Descrição',
                'Valor',
                'Data de Vencimento',
                'Data de Pagamento',
                'Status',
                'Método de Pagamento',
                'Número do Documento',
                'Notas',
                'Edital Relacionado',
                'Criado Por',
                'Data de Criação'
            ], ';');

            // Dados
            foreach ($transactions as $trans) {
                fputcsv($handle, [
                    $trans->id,
                    ucfirst($trans->type),
                    $trans->category,
                    $trans->description,
                    'R$ ' . number_format($trans->amount, 2, ',', '.'),
                    Carbon::parse($trans->due_date)->format('d/m/Y'),
                    $trans->payment_date ? Carbon::parse($trans->payment_date)->format('d/m/Y') : '',
                    ucfirst($trans->status),
                    $trans->payment_method ?? '',
                    $trans->document_number ?? '',
                    $trans->notes ?? '',
                    $trans->edict ? $trans->edict->name : '',
                    $trans->creator ? $trans->creator->name : '',
                    Carbon::parse($trans->created_at)->format('d/m/Y H:i')
                ], ';');
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Transfer-Encoding', 'binary')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            \Log::error('Erro ao exportar CSV: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar dados para CSV: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Exportar para PDF
    public function exportPdf(Request $request)
    {
        try {
            // Usar a mesma lógica de filtros do index
            $query = FinancialTransaction::with(['edict', 'creator'])
                ->where('company_id', $request->input('company_id', 1));

            // Aplicar filtros
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('description', 'LIKE', "%{$search}%")
                      ->orWhere('document_number', 'LIKE', "%{$search}%")
                      ->orWhere('notes', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
            }

            $transactions = $query->orderBy('due_date', 'desc')->get();

            // Calcular totais
            $totalReceitas = $transactions->where('type', 'receita')->sum('amount');
            $totalDespesas = $transactions->where('type', 'despesa')->sum('amount');
            $saldo = $totalReceitas - $totalDespesas;

            // Gerar HTML para PDF
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h1 { color: #2563eb; text-align: center; margin-bottom: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .summary-item { display: inline-block; margin: 0 20px; }
        .summary-label { font-weight: bold; color: #6b7280; }
        .summary-value { font-size: 16px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #2563eb; color: white; padding: 10px; text-align: left; font-size: 11px; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .receita { color: #059669; font-weight: bold; }
        .despesa { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Relatório Financeiro</h1>
    <div class="header">
        <p>Data de geração: ' . date('d/m/Y H:i:s') . '</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <span class="summary-label">Total de Receitas:</span>
            <span class="summary-value receita">R$ ' . number_format($totalReceitas, 2, ',', '.') . '</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total de Despesas:</span>
            <span class="summary-value despesa">R$ ' . number_format($totalDespesas, 2, ',', '.') . '</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Saldo:</span>
            <span class="summary-value" style="color: ' . ($saldo >= 0 ? '#059669' : '#dc2626') . '">R$ ' . number_format($saldo, 2, ',', '.') . '</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

            foreach ($transactions as $trans) {
                $html .= '<tr>
                    <td>' . Carbon::parse($trans->due_date)->format('d/m/Y') . '</td>
                    <td>' . ucfirst($trans->type) . '</td>
                    <td>' . htmlspecialchars($trans->category) . '</td>
                    <td>' . htmlspecialchars($trans->description) . '</td>
                    <td class="' . $trans->type . '">R$ ' . number_format($trans->amount, 2, ',', '.') . '</td>
                    <td>' . ucfirst($trans->status) . '</td>
                </tr>';
            }

            $html .= '</tbody>
    </table>

    <div class="footer">
        <p>Relatório gerado pelo Sistema Evolution CRM - Total de ' . count($transactions) . ' transações</p>
    </div>
</body>
</html>';

            // Retornar HTML que pode ser convertido em PDF pelo navegador
            return response($html, 200)
                ->header('Content-Type', 'text/html; charset=UTF-8');

        } catch (\Exception $e) {
            \Log::error('Erro ao exportar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar dados: ' . $e->getMessage(),
            ], 500);
        }
    }
}
