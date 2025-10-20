<?php

namespace App\Http\Controllers;

use App\Models\Edict;
use App\Models\CompanyDocument;
use App\Models\FinancialTransaction;
use App\Models\ChatMessage;
use App\Services\ClaudeService;
use App\Services\PNCPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected $claudeService;
    protected $pncpService;

    public function __construct(ClaudeService $claudeService, PNCPService $pncpService)
    {
        $this->claudeService = $claudeService;
        $this->pncpService = $pncpService;
    }

    /**
     * Processa mensagem do chat e retorna resposta da IA
     */
    public function message(Request $request)
    {
        try {
            $message = $request->input('message');
            $companyId = $request->input('company_id', 1);
            $sessionId = $request->input('session_id', Str::uuid());

            Log::info('Chat message received', ['message' => $message, 'company_id' => $companyId, 'session_id' => $sessionId]);

            // Analisa a intenção da mensagem
            $intent = $this->analyzeIntent($message);

            // Obtém contexto do histórico
            $historyContext = ChatMessage::getContextFromHistory($sessionId, 5);

            // Tenta usar Claude API para perguntas gerais ou complexas
            if ($intent === 'geral' && $this->claudeService->isConfigured()) {
                $systemContext = $this->getSystemContext($companyId);
                $systemContext['history'] = $historyContext;
                $claudeResponse = $this->claudeService->sendMessage($message, $systemContext);

                if ($claudeResponse) {
                    // Salva no histórico
                    $this->saveMessage($companyId, $sessionId, $message, $claudeResponse, $intent);

                    return response()->json([
                        'success' => true,
                        'response' => $claudeResponse,
                        'data' => null,
                        'session_id' => $sessionId,
                        'powered_by' => 'claude'
                    ]);
                }
            }

            // Processa com base de conhecimento local
            $response = $this->processMessage($message, $intent, $companyId);

            // Salva no histórico
            $this->saveMessage($companyId, $sessionId, $message, $response['text'], $intent, $response['data'] ?? null);

            return response()->json([
                'success' => true,
                'response' => $response['text'],
                'data' => $response['data'] ?? null,
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar mensagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload e análise de documento
     */
    public function uploadDocument(Request $request)
    {
        try {
            $request->validate([
                'document' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB max
                'company_id' => 'required|integer',
                'session_id' => 'required|string'
            ]);

            $file = $request->file('document');
            $companyId = $request->input('company_id');
            $sessionId = $request->input('session_id');

            // Salva o arquivo temporariamente
            $path = $file->store('chat_uploads', 'public');
            $fullPath = storage_path('app/public/' . $path);

            $response = "📄 **Análise de Documento**\n\n";
            $response .= "**Arquivo:** " . $file->getClientOriginalName() . "\n";
            $response .= "**Tamanho:** " . number_format($file->getSize() / 1024, 2) . " KB\n\n";

            // Tenta analisar com Claude se for PDF ou texto
            if ($this->claudeService->isConfigured()) {
                $extension = $file->getClientOriginalExtension();

                if ($extension === 'txt') {
                    // Lê texto simples
                    $content = file_get_contents($fullPath);

                    $analysisPrompt = "Analise este documento e forneça:\n\n";
                    $analysisPrompt .= "1. Resumo do conteúdo\n";
                    $analysisPrompt .= "2. Principais pontos\n";
                    $analysisPrompt .= "3. Se for um edital de licitação, extraia:\n";
                    $analysisPrompt .= "   - Número do edital\n";
                    $analysisPrompt .= "   - Órgão\n";
                    $analysisPrompt .= "   - Objeto\n";
                    $analysisPrompt .= "   - Modalidade\n";
                    $analysisPrompt .= "   - Valor estimado\n";
                    $analysisPrompt .= "   - Datas importantes\n\n";
                    $analysisPrompt .= "Documento:\n" . substr($content, 0, 8000); // Limita para não exceder tokens

                    $analysis = $this->claudeService->sendMessage($analysisPrompt, []);

                    if ($analysis) {
                        $response .= "**✨ Análise Inteligente:**\n\n" . $analysis;

                        // Salva no histórico
                        $this->saveMessage(
                            $companyId,
                            $sessionId,
                            "Upload de documento: " . $file->getClientOriginalName(),
                            $response,
                            'upload_document',
                            ['file_path' => $path, 'analysis' => $analysis]
                        );

                        return response()->json([
                            'success' => true,
                            'response' => $response,
                            'file_path' => $path,
                            'analysis' => $analysis,
                            'session_id' => $sessionId
                        ]);
                    }
                } else if ($extension === 'pdf') {
                    $response .= "📌 **Documento PDF recebido!**\n\n";
                    $response .= "Para análise completa de PDFs, recomendo:\n\n";
                    $response .= "1. **Extrair texto do PDF** usando ferramentas como Adobe ou PDF readers\n";
                    $response .= "2. **Copiar o conteúdo** e colar em uma mensagem para que eu possa analisar\n";
                    $response .= "3. **Ou converter para TXT** e fazer upload novamente\n\n";
                    $response .= "💡 **Dica:** Se for um edital de licitação, posso ajudá-lo a extrair as informações mais importantes!";
                }
            } else {
                $response .= "📝 **Documento recebido com sucesso!**\n\n";
                $response .= "Para análise automática, configure a integração com Claude API.\n\n";
                $response .= "Por enquanto, você pode:\n";
                $response .= "- Descrever o conteúdo do documento\n";
                $response .= "- Me perguntar sobre informações específicas\n";
                $response .= "- Pedir ajuda para interpretar cláusulas";
            }

            // Salva no histórico
            $this->saveMessage(
                $companyId,
                $sessionId,
                "Upload de documento: " . $file->getClientOriginalName(),
                $response,
                'upload_document',
                ['file_path' => $path]
            );

            return response()->json([
                'success' => true,
                'response' => $response,
                'file_path' => $path,
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload do documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salva mensagem no histórico
     */
    private function saveMessage($companyId, $sessionId, $message, $response, $intent, $data = null)
    {
        try {
            ChatMessage::create([
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'session_id' => $sessionId,
                'role' => 'user',
                'message' => $message,
                'response' => $response,
                'intent' => $intent,
                'metadata' => $data ? ['data' => $data] : null,
                'has_attachments' => isset($data['file_path'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving chat message: ' . $e->getMessage());
        }
    }

    /**
     * Obtém contexto do sistema para enriquecer respostas da IA
     */
    private function getSystemContext($companyId): array
    {
        return [
            'editais_count' => Edict::where('company_id', $companyId)->count(),
            'saldo_financeiro' => FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'receita')
                ->sum('amount') - FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'despesa')
                ->sum('amount'),
            'documentos_vencendo' => CompanyDocument::where('company_id', $companyId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->count()
        ];
    }

    /**
     * Analisa a intenção da mensagem do usuário
     */
    private function analyzeIntent($message)
    {
        $messageLower = mb_strtolower($message, 'UTF-8');

        // Normaliza acentos para facilitar matching
        $messageLower = $this->removeAccents($messageLower);

        // Editais - listar abertos
        if (
            (strpos($messageLower, 'edita') !== false && (strpos($messageLower, 'aberto') !== false || strpos($messageLower, 'andamento') !== false)) ||
            (strpos($messageLower, 'quais') !== false && strpos($messageLower, 'edita') !== false && strpos($messageLower, 'aberto') !== false) ||
            (strpos($messageLower, 'licita') !== false && strpos($messageLower, 'aberta') !== false)
        ) {
            return 'listar_editais_abertos';
        }

        // Editais - contar
        if (
            (strpos($messageLower, 'quanto') !== false && strpos($messageLower, 'edita') !== false) ||
            (strpos($messageLower, 'quantos') !== false && strpos($messageLower, 'edita') !== false) ||
            (strpos($messageLower, 'numero') !== false && strpos($messageLower, 'edita') !== false) ||
            (strpos($messageLower, 'total') !== false && strpos($messageLower, 'edita') !== false)
        ) {
            return 'contar_editais';
        }

        // Editais - buscar
        if (
            (strpos($messageLower, 'buscar') !== false && strpos($messageLower, 'edital') !== false) ||
            (strpos($messageLower, 'procurar') !== false && strpos($messageLower, 'edital') !== false) ||
            (strpos($messageLower, 'encontrar') !== false && strpos($messageLower, 'edital') !== false) ||
            (strpos($messageLower, 'pesquisar') !== false && strpos($messageLower, 'edital') !== false)
        ) {
            return 'buscar_editais';
        }

        // === AÇÕES (cadastrar, registrar, criar) - TEM PRIORIDADE ===

        // Ações - Cadastrar transação financeira
        if (
            (strpos($messageLower, 'cadastr') !== false && (strpos($messageLower, 'receita') !== false || strpos($messageLower, 'despesa') !== false || strpos($messageLower, 'financeiro') !== false)) ||
            (strpos($messageLower, 'registrar') !== false && (strpos($messageLower, 'receita') !== false || strpos($messageLower, 'despesa') !== false || strpos($messageLower, 'pagamento') !== false || strpos($messageLower, 'recebimento') !== false)) ||
            (strpos($messageLower, 'criar') !== false && (strpos($messageLower, 'receita') !== false || strpos($messageLower, 'despesa') !== false || strpos($messageLower, 'transacao') !== false)) ||
            (strpos($messageLower, 'lancar') !== false && strpos($messageLower, 'financeiro') !== false) ||
            (strpos($messageLower, 'adicionar') !== false && (strpos($messageLower, 'receita') !== false || strpos($messageLower, 'despesa') !== false))
        ) {
            return 'cadastrar_financeiro';
        }

        // Ações - Cadastrar edital
        if (
            (strpos($messageLower, 'cadastr') !== false && strpos($messageLower, 'edital') !== false) ||
            (strpos($messageLower, 'criar') !== false && strpos($messageLower, 'edital') !== false) ||
            (strpos($messageLower, 'adicionar') !== false && strpos($messageLower, 'edital') !== false) ||
            (strpos($messageLower, 'registrar') !== false && strpos($messageLower, 'edital') !== false)
        ) {
            return 'cadastrar_edital';
        }

        // Guias - Como cadastrar (passo a passo)
        if (
            (strpos($messageLower, 'como') !== false && strpos($messageLower, 'cadastr') !== false) ||
            (strpos($messageLower, 'ensina') !== false && strpos($messageLower, 'cadastr') !== false) ||
            (strpos($messageLower, 'tutorial') !== false) ||
            (strpos($messageLower, 'passo a passo') !== false) ||
            (strpos($messageLower, 'me mostra') !== false && strpos($messageLower, 'cadastr') !== false) ||
            (strpos($messageLower, 'como faco') !== false && strpos($messageLower, 'cadastr') !== false)
        ) {
            return 'guia_cadastro';
        }

        // === CONSULTAS (ver, mostrar dados existentes) ===

        // Financeiro - resumo
        if (
            strpos($messageLower, 'resumo financeiro') !== false ||
            strpos($messageLower, 'situacao financeira') !== false ||
            strpos($messageLower, 'balanco') !== false ||
            strpos($messageLower, 'financas') !== false ||
            (strpos($messageLower, 'saldo') !== false && strpos($messageLower, 'sistema') !== false)
        ) {
            return 'resumo_financeiro';
        }

        // Financeiro - despesas (somente consulta)
        if (
            (strpos($messageLower, 'despesa') !== false || strpos($messageLower, 'gasto') !== false || strpos($messageLower, 'pagar') !== false) &&
            // Excluir se for ação de cadastro
            strpos($messageLower, 'cadastr') === false &&
            strpos($messageLower, 'registrar') === false &&
            strpos($messageLower, 'criar') === false &&
            strpos($messageLower, 'adicionar') === false &&
            strpos($messageLower, 'lancar') === false
        ) {
            return 'informacoes_despesas';
        }

        // Financeiro - receitas (somente consulta)
        if (
            (strpos($messageLower, 'receita') !== false || strpos($messageLower, 'entrada') !== false || strpos($messageLower, 'receber') !== false || strpos($messageLower, 'ganho') !== false) &&
            // Excluir se for ação de cadastro
            strpos($messageLower, 'cadastr') === false &&
            strpos($messageLower, 'registrar') === false &&
            strpos($messageLower, 'criar') === false &&
            strpos($messageLower, 'adicionar') === false &&
            strpos($messageLower, 'lancar') === false
        ) {
            return 'informacoes_receitas';
        }

        // Documentos
        if (
            (strpos($messageLower, 'documento') !== false && strpos($messageLower, 'venc') !== false) ||
            (strpos($messageLower, 'documento') !== false && strpos($messageLower, 'expir') !== false) ||
            (strpos($messageLower, 'documento') !== false && strpos($messageLower, 'prazo') !== false)
        ) {
            return 'documentos_vencimento';
        }

        // Conhecimento - explicar licitação
        if (
            strpos($messageLower, 'como funciona') !== false ||
            (strpos($messageLower, 'o que') !== false && strpos($messageLower, 'licitacao') !== false) ||
            (strpos($messageLower, 'explica') !== false && strpos($messageLower, 'licitacao') !== false) ||
            strpos($messageLower, 'o que e licitacao') !== false
        ) {
            return 'explicar_licitacao';
        }

        // Conhecimento - tipos de licitação
        if (
            strpos($messageLower, 'tipo') !== false && strpos($messageLower, 'licitacao') !== false
        ) {
            return 'tipos_licitacao';
        }

        // Conhecimento - modalidades
        if (
            strpos($messageLower, 'modalidade') !== false ||
            strpos($messageLower, 'pregao') !== false ||
            strpos($messageLower, 'concorrencia') !== false ||
            strpos($messageLower, 'leilao') !== false
        ) {
            return 'modalidades_licitacao';
        }

        // Conhecimento - legislação
        if (
            strpos($messageLower, 'lei') !== false ||
            strpos($messageLower, '8666') !== false ||
            strpos($messageLower, '14133') !== false ||
            strpos($messageLower, 'legislacao') !== false
        ) {
            return 'legislacao_licitacao';
        }

        // Conhecimento - prazos
        if (
            strpos($messageLower, 'prazo') !== false ||
            strpos($messageLower, 'tempo') !== false ||
            strpos($messageLower, 'cronograma') !== false
        ) {
            return 'prazos_licitacao';
        }

        // Conhecimento - documentação
        if (
            (strpos($messageLower, 'documento') !== false && strpos($messageLower, 'necessario') !== false) ||
            (strpos($messageLower, 'documento') !== false && strpos($messageLower, 'habilitacao') !== false) ||
            strpos($messageLower, 'documentacao') !== false
        ) {
            return 'documentacao_necessaria';
        }

        // Conhecimento - fases
        if (
            strpos($messageLower, 'fase') !== false ||
            strpos($messageLower, 'etapa') !== false ||
            strpos($messageLower, 'processo') !== false
        ) {
            return 'fases_licitacao';
        }

        // Conhecimento - recursos
        if (
            strpos($messageLower, 'recurso') !== false ||
            strpos($messageLower, 'impugnacao') !== false ||
            strpos($messageLower, 'contestacao') !== false
        ) {
            return 'recursos_impugnacoes';
        }

        // Conhecimento - SRP
        if (
            strpos($messageLower, 'srp') !== false ||
            strpos($messageLower, 'registro de preco') !== false ||
            strpos($messageLower, 'ata de registro') !== false
        ) {
            return 'sistema_registro_precos';
        }

        // Conhecimento - glossário
        if (
            strpos($messageLower, 'glossario') !== false ||
            strpos($messageLower, 'termo') !== false ||
            strpos($messageLower, 'significa') !== false ||
            (strpos($messageLower, 'o que') !== false && strpos($messageLower, 'e ') !== false)
        ) {
            return 'glossario';
        }

        // PNCP - buscar contratos
        if (
            strpos($messageLower, 'pncp') !== false ||
            (strpos($messageLower, 'buscar') !== false && strpos($messageLower, 'contrato') !== false) ||
            (strpos($messageLower, 'consultar') !== false && strpos($messageLower, 'contrato') !== false) ||
            strpos($messageLower, 'contratos publicos') !== false ||
            strpos($messageLower, 'portal nacional') !== false
        ) {
            return 'pncp_contratos';
        }

        // PNCP - buscar avisos/editais
        if (
            (strpos($messageLower, 'buscar') !== false && strpos($messageLower, 'aviso') !== false) ||
            (strpos($messageLower, 'consultar') !== false && strpos($messageLower, 'aviso') !== false) ||
            (strpos($messageLower, 'editais') !== false && strpos($messageLower, 'governo') !== false) ||
            strpos($messageLower, 'avisos publicos') !== false
        ) {
            return 'pncp_avisos';
        }

        // PNCP - estatísticas
        if (
            (strpos($messageLower, 'estatistica') !== false && strpos($messageLower, 'pncp') !== false) ||
            (strpos($messageLower, 'dados') !== false && strpos($messageLower, 'governo') !== false) ||
            strpos($messageLower, 'estatisticas publicas') !== false
        ) {
            return 'pncp_estatisticas';
        }

        // Perguntas sobre capacidades
        if (
            (strpos($messageLower, 'consegue') !== false || strpos($messageLower, 'pode') !== false ||
             strpos($messageLower, 'voce faz') !== false || strpos($messageLower, 'capaz') !== false) &&
            (strpos($messageLower, 'cadastr') !== false || strpos($messageLower, 'fazer') !== false ||
             strpos($messageLower, 'criar') !== false || strpos($messageLower, 'ajudar') !== false)
        ) {
            return 'capacidades';
        }

        // Comando /menu
        if (strpos($messageLower, '/menu') !== false) {
            return 'menu';
        }

        return 'geral';
    }

    /**
     * Remove acentos de uma string
     */
    private function removeAccents($string)
    {
        $unwanted = [
            'á'=>'a', 'à'=>'a', 'ã'=>'a', 'â'=>'a', 'é'=>'e', 'ê'=>'e', 'í'=>'i',
            'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ú'=>'u', 'ü'=>'u', 'ç'=>'c',
            'Á'=>'a', 'À'=>'a', 'Ã'=>'a', 'Â'=>'a', 'É'=>'e', 'Ê'=>'e', 'Í'=>'i',
            'Ó'=>'o', 'Ô'=>'o', 'Õ'=>'o', 'Ú'=>'u', 'Ü'=>'u', 'Ç'=>'c'
        ];
        return strtr($string, $unwanted);
    }

    /**
     * Processa a mensagem com base na intenção
     */
    private function processMessage($message, $intent, $companyId)
    {
        switch ($intent) {
            case 'listar_editais_abertos':
                return $this->listarEditaisAbertos($companyId);

            case 'contar_editais':
                return $this->contarEditais($companyId);

            case 'buscar_editais':
                return $this->buscarEditais($message, $companyId);

            case 'resumo_financeiro':
                return $this->resumoFinanceiro($companyId);

            case 'informacoes_despesas':
                return $this->informacoesDespesas($companyId);

            case 'informacoes_receitas':
                return $this->informacoesReceitas($companyId);

            case 'documentos_vencimento':
                return $this->documentosVencimento($companyId);

            case 'explicar_licitacao':
                return $this->explicarLicitacao();

            case 'tipos_licitacao':
                return $this->tiposLicitacao();

            case 'modalidades_licitacao':
                return $this->modalidadesLicitacao();

            case 'legislacao_licitacao':
                return $this->legislacaoLicitacao();

            case 'prazos_licitacao':
                return $this->prazosLicitacao();

            case 'documentacao_necessaria':
                return $this->documentacaoNecessaria();

            case 'fases_licitacao':
                return $this->fasesLicitacao();

            case 'recursos_impugnacoes':
                return $this->recursosImpugnacoes();

            case 'sistema_registro_precos':
                return $this->sistemaRegistroPrecos();

            case 'glossario':
                return $this->glossario($message);

            case 'pncp_contratos':
                return $this->buscarContratosPNCP($message);

            case 'pncp_avisos':
                return $this->buscarAvisosPNCP($message);

            case 'pncp_estatisticas':
                return $this->estatisticasPNCP();

            case 'cadastrar_edital':
                return $this->cadastrarEdital($message, $companyId);

            case 'cadastrar_financeiro':
                return $this->cadastrarFinanceiro($message, $companyId);

            case 'guia_cadastro':
                return $this->guiaCadastro($message);

            case 'capacidades':
                return $this->explicarCapacidades();

            case 'menu':
                return $this->mostrarMenu();

            default:
                return $this->respostaGeral($message);
        }
    }

    // ===== MÉTODOS DE DADOS DO SISTEMA =====

    private function listarEditaisAbertos($companyId)
    {
        $editais = Edict::where('company_id', $companyId)
            ->whereIn('status', ['imported', 'analyzed'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'edict_number', 'organ', 'status', 'closing_date']);

        if ($editais->isEmpty()) {
            return [
                'text' => 'Atualmente não há editais abertos no sistema. Você pode fazer upload de novos editais na seção de Licitações.'
            ];
        }

        $text = "Encontrei **" . $editais->count() . " editais** em andamento:\n\n";
        foreach ($editais as $edict) {
            $text .= "- **{$edict->edict_number}** - {$edict->organ}\n";
            if ($edict->closing_date) {
                $text .= "  Prazo: " . \Carbon\Carbon::parse($edict->closing_date)->format('d/m/Y') . "\n";
            }
        }

        return [
            'text' => $text,
            'data' => ['editais' => $editais]
        ];
    }

    private function contarEditais($companyId)
    {
        $total = Edict::where('company_id', $companyId)->count();
        $abertos = Edict::where('company_id', $companyId)
            ->whereIn('status', ['imported', 'analyzed'])
            ->count();
        $fechados = Edict::where('company_id', $companyId)
            ->where('status', 'closed')
            ->count();

        $text = "📊 **Estatísticas de Editais:**\n\n";
        $text .= "- Total de editais: **{$total}**\n";
        $text .= "- Editais abertos: **{$abertos}**\n";
        $text .= "- Editais fechados: **{$fechados}**\n";

        return ['text' => $text];
    }

    private function buscarEditais($message, $companyId)
    {
        // Extrai possíveis termos de busca
        $termos = preg_replace('/buscar|procurar|encontrar|edital|sobre/i', '', $message);
        $termos = trim($termos);

        if (empty($termos)) {
            return $this->listarEditaisAbertos($companyId);
        }

        $editais = Edict::where('company_id', $companyId)
            ->where(function($query) use ($termos) {
                $query->where('edict_number', 'like', "%{$termos}%")
                      ->orWhere('organ', 'like', "%{$termos}%")
                      ->orWhere('description', 'like', "%{$termos}%");
            })
            ->limit(5)
            ->get(['id', 'edict_number', 'organ', 'status']);

        if ($editais->isEmpty()) {
            return [
                'text' => "Não encontrei editais relacionados a '{$termos}'. Tente buscar por outro termo ou verifique os editais cadastrados."
            ];
        }

        $text = "Encontrei **" . $editais->count() . " editais** relacionados a '{$termos}':\n\n";
        foreach ($editais as $edict) {
            $text .= "- **{$edict->edict_number}** - {$edict->organ}\n";
        }

        return [
            'text' => $text,
            'data' => ['editais' => $editais]
        ];
    }

    private function resumoFinanceiro($companyId)
    {
        $receitas = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'receita')
            ->whereIn('status', ['recebido', 'pago'])
            ->sum('amount');

        $despesas = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'despesa')
            ->whereIn('status', ['pago', 'recebido'])
            ->sum('amount');

        $pendentes = FinancialTransaction::where('company_id', $companyId)
            ->where('status', 'pendente')
            ->count();

        $saldo = $receitas - $despesas;

        $text = "💰 **Resumo Financeiro:**\n\n";
        $text .= "- Receitas: **R$ " . number_format($receitas, 2, ',', '.') . "**\n";
        $text .= "- Despesas: **R$ " . number_format($despesas, 2, ',', '.') . "**\n";
        $text .= "- Saldo: **R$ " . number_format($saldo, 2, ',', '.') . "**\n";
        $text .= "- Transações pendentes: **{$pendentes}**\n";

        return [
            'text' => $text,
            'data' => ['stats' => "Saldo atual: R$ " . number_format($saldo, 2, ',', '.')]
        ];
    }

    private function informacoesDespesas($companyId)
    {
        $despesas = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'despesa')
            ->whereIn('status', ['pago', 'recebido'])
            ->sum('amount');

        $maiores = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'despesa')
            ->orderBy('amount', 'desc')
            ->limit(3)
            ->get(['description', 'amount', 'category']);

        $text = "💸 **Informações sobre Despesas:**\n\n";
        $text .= "- Total de despesas pagas: **R$ " . number_format($despesas, 2, ',', '.') . "**\n\n";

        if ($maiores->isNotEmpty()) {
            $text .= "**Maiores despesas:**\n";
            foreach ($maiores as $despesa) {
                $text .= "- {$despesa->description}: R$ " . number_format($despesa->amount, 2, ',', '.') . "\n";
            }
        }

        return ['text' => $text];
    }

    private function informacoesReceitas($companyId)
    {
        $receitas = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'receita')
            ->whereIn('status', ['recebido', 'pago'])
            ->sum('amount');

        $text = "💵 **Informações sobre Receitas:**\n\n";
        $text .= "- Total de receitas recebidas: **R$ " . number_format($receitas, 2, ',', '.') . "**\n";

        return ['text' => $text];
    }

    private function documentosVencimento($companyId)
    {
        $vencendo = CompanyDocument::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->get(['id', 'document_type', 'expiry_date']);

        if ($vencendo->isEmpty()) {
            return [
                'text' => '✅ Não há documentos vencendo nos próximos 30 dias.'
            ];
        }

        $text = "⚠️ **Documentos vencendo em 30 dias:**\n\n";
        foreach ($vencendo as $doc) {
            $dias = now()->diffInDays($doc->expiry_date);
            $text .= "- {$doc->document_type}: vence em {$dias} dias (" . \Carbon\Carbon::parse($doc->expiry_date)->format('d/m/Y') . ")\n";
        }

        return ['text' => $text];
    }

    // ===== MÉTODOS DE CONHECIMENTO GERAL =====

    private function explicarLicitacao()
    {
        $text = "📚 **O que é uma Licitação Pública?**\n\n";
        $text .= "Licitação é o procedimento administrativo formal pelo qual a Administração Pública seleciona a proposta mais vantajosa para contratação de obras, serviços, compras ou alienações.\n\n";
        $text .= "**Princípios fundamentais:**\n";
        $text .= "- *Legalidade*: seguir as leis vigentes\n";
        $text .= "- *Impessoalidade*: tratamento igual a todos\n";
        $text .= "- *Moralidade*: conduta ética\n";
        $text .= "- *Publicidade*: transparência nos atos\n";
        $text .= "- *Eficiência*: melhor resultado\n";
        $text .= "- *Isonomia*: igualdade de condições\n\n";
        $text .= "A licitação garante a competitividade e impede favorecimentos na administração pública.";

        return ['text' => $text];
    }

    private function tiposLicitacao()
    {
        $text = "📋 **Tipos de Licitação (Critérios de Julgamento):**\n\n";
        $text .= "1. **Menor Preço**\n";
        $text .= "   - O mais comum\n";
        $text .= "   - Vence quem oferece o menor valor\n\n";
        $text .= "2. **Melhor Técnica**\n";
        $text .= "   - Avalia qualidade técnica\n";
        $text .= "   - Usado em serviços especializados\n\n";
        $text .= "3. **Técnica e Preço**\n";
        $text .= "   - Combina qualidade técnica e preço\n";
        $text .= "   - Pontuação ponderada\n\n";
        $text .= "4. **Maior Lance ou Oferta**\n";
        $text .= "   - Para alienação de bens ou concessão\n";
        $text .= "   - Vence quem oferece mais";

        return ['text' => $text];
    }

    private function modalidadesLicitacao()
    {
        $text = "🏛️ **Modalidades de Licitação (Lei 14.133/2021):**\n\n";
        $text .= "1. **Pregão**\n";
        $text .= "   - Para bens e serviços comuns\n";
        $text .= "   - Mais ágil e competitivo\n";
        $text .= "   - Fase de lances verbais ou eletrônicos\n\n";
        $text .= "2. **Concorrência**\n";
        $text .= "   - Obras e serviços de engenharia\n";
        $text .= "   - Valores mais altos\n";
        $text .= "   - Mais formal e demorada\n\n";
        $text .= "3. **Concurso**\n";
        $text .= "   - Trabalhos técnicos, científicos ou artísticos\n";
        $text .= "   - Prêmio ou remuneração\n\n";
        $text .= "4. **Leilão**\n";
        $text .= "   - Venda de bens móveis inservíveis\n";
        $text .= "   - Produtos legalmente apreendidos\n\n";
        $text .= "5. **Diálogo Competitivo**\n";
        $text .= "   - Inovações ou soluções alternativas\n";
        $text .= "   - Quando não é possível definir especificações precisas";

        return ['text' => $text];
    }

    private function legislacaoLicitacao()
    {
        $text = "⚖️ **Legislação de Licitações:**\n\n";
        $text .= "**Lei 14.133/2021 (Nova Lei de Licitações)**\n";
        $text .= "- Vigente desde 1º de abril de 2021\n";
        $text .= "- Unifica as normas de licitações e contratos\n";
        $text .= "- Substitui gradualmente as leis 8.666/93 e 10.520/02\n";
        $text .= "- Traz inovações como:\n";
        $text .= "  • Diálogo competitivo\n";
        $text .= "  • Credenciamento\n";
        $text .= "  • Contratação integrada\n";
        $text .= "  • Maior uso de tecnologia\n\n";
        $text .= "**Lei 8.666/1993 (Lei antiga)**\n";
        $text .= "- Em vigor até 31/03/2023 para novos processos\n";
        $text .= "- Contratos em andamento seguem essa lei\n\n";
        $text .= "**Transição:**\n";
        $text .= "- Administrações podem escolher qual lei usar até 2023\n";
        $text .= "- A partir de 2024, apenas Lei 14.133/2021";

        return ['text' => $text];
    }

    private function prazosLicitacao()
    {
        $text = "⏰ **Prazos em Licitações (Lei 14.133/2021):**\n\n";
        $text .= "**Publicação do Edital:**\n";
        $text .= "- Pregão eletrônico: mínimo 8 dias úteis\n";
        $text .= "- Concorrência: mínimo 30 dias corridos\n";
        $text .= "- Leilão: mínimo 15 dias corridos\n\n";
        $text .= "**Impugnação ao Edital:**\n";
        $text .= "- Até 3 dias úteis antes da abertura\n";
        $text .= "- Resposta em até 3 dias úteis\n\n";
        $text .= "**Recursos Administrativos:**\n";
        $text .= "- Interposição: 3 dias úteis\n";
        $text .= "- Contrarrazões: 3 dias úteis\n";
        $text .= "- Análise: 10 dias úteis\n\n";
        $text .= "**Homologação e Adjudicação:**\n";
        $text .= "- Após fase recursal ou desistência\n";
        $text .= "- Validade da proposta: geralmente 60 dias\n\n";
        $text .= "**Assinatura do Contrato:**\n";
        $text .= "- Até 60 dias após homologação\n";
        $text .= "- Prorrogável por igual período";

        return ['text' => $text];
    }

    private function documentacaoNecessaria()
    {
        $text = "📄 **Documentação para Habilitação:**\n\n";
        $text .= "**1. Habilitação Jurídica:**\n";
        $text .= "- Registro comercial (Junta Comercial)\n";
        $text .= "- Ato constitutivo e alterações\n";
        $text .= "- CPF e RG dos sócios (se aplicável)\n\n";
        $text .= "**2. Regularidade Fiscal:**\n";
        $text .= "- ✅ CND Federal (Receita Federal)\n";
        $text .= "- ✅ CND FGTS\n";
        $text .= "- ✅ CND Trabalhista (TST)\n";
        $text .= "- ✅ CND Estadual\n";
        $text .= "- ✅ CND Municipal\n\n";
        $text .= "**3. Qualificação Técnica:**\n";
        $text .= "- Atestados de capacidade técnica\n";
        $text .= "- Registro profissional (CREA, etc)\n";
        $text .= "- Certidão de acervo técnico\n\n";
        $text .= "**4. Qualificação Econômico-Financeira:**\n";
        $text .= "- Balanço patrimonial\n";
        $text .= "- Certidão negativa de falência\n";
        $text .= "- Índices contábeis\n\n";
        $text .= "**5. Outros:**\n";
        $text .= "- Declaração de cumprimento dos requisitos\n";
        $text .= "- Declaração de inexistência de menor";

        return ['text' => $text];
    }

    private function fasesLicitacao()
    {
        $text = "🔄 **Fases da Licitação:**\n\n";
        $text .= "**1. Planejamento**\n";
        $text .= "   - Estudo técnico preliminar\n";
        $text .= "   - Termo de referência\n";
        $text .= "   - Estimativa de preços\n\n";
        $text .= "**2. Publicação do Edital**\n";
        $text .= "   - Divulgação ampla\n";
        $text .= "   - Prazo mínimo de publicidade\n\n";
        $text .= "**3. Apresentação de Propostas**\n";
        $text .= "   - Envelope lacrado ou digital\n";
        $text .= "   - Dentro do prazo estabelecido\n\n";
        $text .= "**4. Abertura e Julgamento**\n";
        $text .= "   - Sessão pública\n";
        $text .= "   - Análise das propostas\n";
        $text .= "   - Fase de lances (se pregão)\n\n";
        $text .= "**5. Habilitação**\n";
        $text .= "   - Verificação dos documentos\n";
        $text .= "   - Do melhor classificado\n\n";
        $text .= "**6. Recursos**\n";
        $text .= "   - Prazo para impugnação\n";
        $text .= "   - Análise pela autoridade\n\n";
        $text .= "**7. Adjudicação**\n";
        $text .= "   - Atribuição do objeto ao vencedor\n\n";
        $text .= "**8. Homologação**\n";
        $text .= "   - Aprovação final pela autoridade\n";
        $text .= "   - Validade do processo\n\n";
        $text .= "**9. Contratação**\n";
        $text .= "   - Assinatura do contrato\n";
        $text .= "   - Início da execução";

        return ['text' => $text];
    }

    private function recursosImpugnacoes()
    {
        $text = "⚖️ **Recursos e Impugnações:**\n\n";
        $text .= "**Impugnação ao Edital:**\n";
        $text .= "- **Quem pode:** Qualquer pessoa\n";
        $text .= "- **Prazo:** Até 3 dias úteis antes da abertura\n";
        $text .= "- **Objetivo:** Corrigir irregularidades no edital\n";
        $text .= "- **Efeito:** Pode suspender o processo\n\n";
        $text .= "**Pedido de Esclarecimento:**\n";
        $text .= "- Dúvidas sobre o edital\n";
        $text .= "- Mesmo prazo da impugnação\n";
        $text .= "- Resposta vincula a administração\n\n";
        $text .= "**Recurso Administrativo:**\n";
        $text .= "- **Cabimento:** Contra decisões da comissão\n";
        $text .= "- **Prazo:** 3 dias úteis\n";
        $text .= "- **Efeito:** Geralmente suspensivo\n";
        $text .= "- **Contrarrazões:** 3 dias úteis\n\n";
        $text .= "**Representação:**\n";
        $text .= "- Para TCU ou MP\n";
        $text .= "- Irregularidades graves\n";
        $text .= "- Sem prazo específico\n\n";
        $text .= "**Dicas:**\n";
        $text .= "- ✅ Seja específico e objetivo\n";
        $text .= "- ✅ Cite artigos de lei\n";
        $text .= "- ✅ Apresente fundamentos técnicos\n";
        $text .= "- ✅ Protocole no prazo correto";

        return ['text' => $text];
    }

    private function sistemaRegistroPrecos()
    {
        $text = "🏷️ **Sistema de Registro de Preços (SRP):**\n\n";
        $text .= "**O que é:**\n";
        $text .= "Sistema que permite à Administração Pública registrar preços para futuras contratações, sem compromisso imediato de compra.\n\n";
        $text .= "**Características:**\n";
        $text .= "- Validade: até 12 meses\n";
        $text .= "- Não obriga a contratação\n";
        $text .= "- Permite compras parceladas\n";
        $text .= "- Órgãos podem \"caronar\"\n\n";
        $text .= "**Vantagens:**\n";
        $text .= "✅ Economia de tempo\n";
        $text .= "✅ Preços mais competitivos\n";
        $text .= "✅ Flexibilidade nas compras\n";
        $text .= "✅ Redução de processos\n\n";
        $text .= "**Ata de Registro de Preços:**\n";
        $text .= "- Documento que formaliza os preços\n";
        $text .= "- Vincula fornecedores e administração\n";
        $text .= "- Pode ter múltiplos fornecedores\n\n";
        $text .= "**Adesão (Carona):**\n";
        $text .= "- Outros órgãos podem aderir à ata\n";
        $text .= "- Limitado ao quádruplo do órgão gerenciador\n";
        $text .= "- Requer autorização do beneficiário\n\n";
        $text .= "**Cancelamento:**\n";
        $text .= "- Preços se tornarem incompatíveis\n";
        $text .= "- Descumprimento de condições\n";
        $text .= "- Por razões de interesse público";

        return ['text' => $text];
    }

    private function glossario($message)
    {
        $glossario = [
            'adjudicacao' => "**Adjudicação:** Ato pelo qual se atribui ao vencedor o objeto da licitação.",
            'homologacao' => "**Homologação:** Aprovação final do processo licitatório pela autoridade superior.",
            'habilitacao' => "**Habilitação:** Fase que verifica se o licitante atende aos requisitos para contratar.",
            'edital' => "**Edital:** Documento que estabelece as regras da licitação.",
            'pregao' => "**Pregão:** Modalidade para bens e serviços comuns, com fase de lances.",
            'srp' => "**SRP:** Sistema de Registro de Preços - permite contratar sem licitação específica.",
            'cnd' => "**CND:** Certidão Negativa de Débitos - prova regularidade fiscal.",
            'acervo tecnico' => "**Acervo Técnico:** Conjunto de trabalhos já realizados por profissional.",
            'menor preco' => "**Menor Preço:** Critério onde vence quem oferece o menor valor.",
            'melhor tecnica' => "**Melhor Técnica:** Critério focado na qualidade técnica da proposta.",
        ];

        $messageLower = strtolower($this->removeAccents($message));

        foreach ($glossario as $termo => $definicao) {
            if (strpos($messageLower, $termo) !== false) {
                return ['text' => $definicao];
            }
        }

        // Resposta genérica se não encontrou o termo
        $text = "📖 **Glossário de Licitações:**\n\n";
        $text .= "Pergunte sobre termos como:\n";
        $text .= "- Adjudicação\n";
        $text .= "- Homologação\n";
        $text .= "- Habilitação\n";
        $text .= "- Pregão\n";
        $text .= "- SRP (Sistema de Registro de Preços)\n";
        $text .= "- CND (Certidão Negativa)\n";
        $text .= "- Acervo Técnico\n";
        $text .= "- Menor Preço\n";
        $text .= "- Melhor Técnica\n\n";
        $text .= "Exemplo: \"O que é adjudicação?\"";

        return ['text' => $text];
    }

    // ===== MÉTODOS DE INTEGRAÇÃO PNCP =====

    private function buscarContratosPNCP($message)
    {
        $text = "🔍 **Buscando contratos no PNCP** (Portal Nacional de Contratações Públicas)...\n\n";

        try {
            // Extrai parâmetros da mensagem
            $params = $this->extractSearchParams($message);

            // Busca contratos no PNCP
            $resultado = $this->pncpService->searchContracts($params);

            if (!$resultado || empty($resultado['items'])) {
                $text .= "⚠️ Nenhum contrato encontrado no PNCP com os critérios especificados.\n\n";
                $text .= "**Dicas:**\n";
                $text .= "- Tente buscar por ano específico\n";
                $text .= "- Use palavras-chave mais gerais\n";
                $text .= "- Verifique o CNPJ do órgão\n\n";
                $text .= "Acesse: https://pncp.gov.br";
                return ['text' => $text];
            }

            $text .= "✅ **Encontrei " . ($resultado['totalRegistros'] ?? count($resultado['items'])) . " contratos**\n\n";
            $text .= "**📋 Principais resultados:**\n\n";

            $count = 0;
            foreach ($resultado['items'] as $contrato) {
                if ($count >= 5) break; // Limita a 5 resultados

                $formatted = $this->pncpService->formatContractData($contrato);

                $text .= "**" . ($count + 1) . ". " . $formatted['numero'] . "**\n";
                $text .= "- **Objeto:** " . substr($formatted['objeto'], 0, 100) . "...\n";
                $text .= "- **Órgão:** " . $formatted['orgao'] . "\n";
                $text .= "- **Valor:** " . $formatted['valor_formatado'] . "\n";
                $text .= "- **Fornecedor:** " . $formatted['fornecedor'] . "\n";

                if ($formatted['data_vigencia_fim']) {
                    $text .= "- **Vigência até:** " . date('d/m/Y', strtotime($formatted['data_vigencia_fim'])) . "\n";
                }

                $text .= "\n";
                $count++;
            }

            $text .= "\n🌐 **Fonte:** Portal Nacional de Contratações Públicas (PNCP)";
            $text .= "\n📅 **Última atualização:** " . now()->format('d/m/Y H:i');

            return ['text' => $text, 'data' => $resultado['items']];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar contratos PNCP: ' . $e->getMessage());

            $text .= "❌ **Erro ao consultar o PNCP.**\n\n";
            $text .= "O Portal Nacional pode estar temporariamente indisponível.\n\n";
            $text .= "Tente novamente em alguns minutos ou acesse diretamente:\n";
            $text .= "https://pncp.gov.br";

            return ['text' => $text];
        }
    }

    private function buscarAvisosPNCP($message)
    {
        $text = "📢 **Buscando avisos e editais no PNCP**...\n\n";

        try {
            // Extrai parâmetros da mensagem
            $params = $this->extractSearchParams($message);

            // Adiciona filtro de data recente se não especificado
            if (empty($params['dataInicial'])) {
                $params['dataInicial'] = now()->subDays(30)->format('Y-m-d');
                $params['dataFinal'] = now()->format('Y-m-d');
            }

            // Busca avisos no PNCP
            $resultado = $this->pncpService->searchNotices($params);

            if (!$resultado || empty($resultado['items'])) {
                $text .= "⚠️ Nenhum aviso encontrado no PNCP.\n\n";
                $text .= "**Dicas:**\n";
                $text .= "- Verifique o período de busca\n";
                $text .= "- Tente palavras-chave diferentes\n";
                $text .= "- Especifique a modalidade (pregão, concorrência, etc.)\n\n";
                $text .= "Acesse: https://pncp.gov.br";
                return ['text' => $text];
            }

            $text .= "✅ **Encontrei " . ($resultado['totalRegistros'] ?? count($resultado['items'])) . " avisos**\n\n";
            $text .= "**📋 Principais resultados:**\n\n";

            $count = 0;
            foreach ($resultado['items'] as $aviso) {
                if ($count >= 5) break; // Limita a 5 resultados

                $formatted = $this->pncpService->formatNoticeData($aviso);

                $text .= "**" . ($count + 1) . ". " . $formatted['numero'] . "**\n";
                $text .= "- **Objeto:** " . substr($formatted['titulo'], 0, 100) . "...\n";
                $text .= "- **Órgão:** " . $formatted['orgao'] . "\n";
                $text .= "- **Modalidade:** " . $formatted['modalidade'] . "\n";
                $text .= "- **Valor Estimado:** " . $formatted['valor_formatado'] . "\n";

                if ($formatted['data_abertura']) {
                    $text .= "- **Abertura:** " . date('d/m/Y H:i', strtotime($formatted['data_abertura'])) . "\n";
                }

                $text .= "- **Situação:** " . $formatted['situacao'] . "\n";

                if ($formatted['link_sistema']) {
                    $text .= "- **Link:** " . $formatted['link_sistema'] . "\n";
                }

                $text .= "\n";
                $count++;
            }

            $text .= "\n🌐 **Fonte:** Portal Nacional de Contratações Públicas (PNCP)";
            $text .= "\n📅 **Última atualização:** " . now()->format('d/m/Y H:i');

            return ['text' => $text, 'data' => $resultado['items']];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar avisos PNCP: ' . $e->getMessage());

            $text .= "❌ **Erro ao consultar o PNCP.**\n\n";
            $text .= "O Portal Nacional pode estar temporariamente indisponível.\n\n";
            $text .= "Tente novamente em alguns minutos ou acesse diretamente:\n";
            $text .= "https://pncp.gov.br";

            return ['text' => $text];
        }
    }

    private function estatisticasPNCP()
    {
        $text = "📊 **Estatísticas do PNCP** (Portal Nacional de Contratações Públicas)\n\n";

        try {
            $anoAtual = date('Y');
            $anoAnterior = date('Y') - 1;

            // Busca estatísticas do ano atual e anterior
            $statsAtual = $this->pncpService->getStatistics($anoAtual);
            $statsAnterior = $this->pncpService->getStatistics($anoAnterior);

            if (!$statsAtual) {
                $text .= "⚠️ Não foi possível obter estatísticas do PNCP no momento.\n\n";
                $text .= "Tente novamente mais tarde ou acesse:\n";
                $text .= "https://pncp.gov.br";
                return ['text' => $text];
            }

            $text .= "**📅 Ano {$anoAtual}:**\n";
            $text .= "- Contratos registrados: " . number_format($statsAtual['total_contracts'] ?? 0, 0, ',', '.') . "\n";
            $text .= "- Avisos publicados: " . number_format($statsAtual['total_notices'] ?? 0, 0, ',', '.') . "\n\n";

            if ($statsAnterior) {
                $text .= "**📅 Ano {$anoAnterior}:**\n";
                $text .= "- Contratos registrados: " . number_format($statsAnterior['total_contracts'] ?? 0, 0, ',', '.') . "\n";
                $text .= "- Avisos publicados: " . number_format($statsAnterior['total_notices'] ?? 0, 0, ',', '.') . "\n\n";

                // Calcula variação
                if ($statsAnterior['total_contracts'] > 0) {
                    $variacaoContratos = (($statsAtual['total_contracts'] - $statsAnterior['total_contracts']) / $statsAnterior['total_contracts']) * 100;
                    $icon = $variacaoContratos > 0 ? '📈' : '📉';
                    $text .= "**{$icon} Variação de contratos:** " . number_format($variacaoContratos, 1) . "%\n\n";
                }
            }

            $text .= "**ℹ️ Sobre o PNCP:**\n";
            $text .= "O Portal Nacional de Contratações Públicas (PNCP) é a plataforma oficial do Governo Federal ";
            $text .= "para divulgação centralizada e obrigatória de licitações e contratos públicos, ";
            $text .= "conforme estabelecido pela Lei 14.133/2021.\n\n";

            $text .= "🌐 **Acesse:** https://pncp.gov.br";
            $text .= "\n📅 **Última atualização:** " . now()->format('d/m/Y H:i');

            return ['text' => $text, 'data' => ['atual' => $statsAtual, 'anterior' => $statsAnterior]];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas PNCP: ' . $e->getMessage());

            $text .= "❌ **Erro ao consultar o PNCP.**\n\n";
            $text .= "O Portal Nacional pode estar temporariamente indisponível.\n\n";
            $text .= "Acesse diretamente: https://pncp.gov.br";

            return ['text' => $text];
        }
    }

    /**
     * Extrai parâmetros de busca da mensagem do usuário
     */
    private function extractSearchParams($message): array
    {
        $params = [];
        $messageLower = mb_strtolower($message, 'UTF-8');

        // Extrai ano
        if (preg_match('/\b(20\d{2})\b/', $message, $matches)) {
            $params['year'] = $matches[1];
        }

        // Extrai UF
        $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
        foreach ($ufs as $uf) {
            if (strpos($messageLower, strtolower($uf)) !== false) {
                $params['uf'] = $uf;
                break;
            }
        }

        // Extrai modalidade
        $modalidades = ['pregao', 'pregão', 'concorrencia', 'concorrência', 'leilao', 'leilão', 'concurso'];
        foreach ($modalidades as $modalidade) {
            if (strpos($messageLower, $modalidade) !== false) {
                $params['modalidade'] = $modalidade;
                break;
            }
        }

        // Extrai palavras-chave (remove palavras comuns)
        $palavrasComuns = ['buscar', 'consultar', 'pncp', 'contrato', 'aviso', 'edital', 'sobre', 'de', 'da', 'do', 'para', 'em'];
        $palavras = explode(' ', $messageLower);
        $palavrasChave = array_diff($palavras, $palavrasComuns);

        if (!empty($palavrasChave)) {
            $params['search'] = implode(' ', array_slice($palavrasChave, 0, 3)); // Máximo 3 palavras
        }

        return $params;
    }

    // ===== MÉTODOS DE CADASTRO VIA IA =====

    private function cadastrarEdital($message, $companyId)
    {
        $text = "📝 **Cadastro de Edital via IA**\n\n";

        // Usa Claude para extrair informações
        if ($this->claudeService->isConfigured()) {
            $prompt = "Extraia as seguintes informações desta solicitação de cadastro de edital:\n\n";
            $prompt .= "Mensagem: {$message}\n\n";
            $prompt .= "Retorne um JSON com os campos:\n";
            $prompt .= "- edict_number (número do edital)\n";
            $prompt .= "- organ (órgão)\n";
            $prompt .= "- object (objeto da licitação)\n";
            $prompt .= "- modality (modalidade: pregao, concorrencia, etc)\n";
            $prompt .= "- value (valor estimado em número)\n";
            $prompt .= "- opening_date (data de abertura YYYY-MM-DD)\n";
            $prompt .= "- closing_date (data de fechamento YYYY-MM-DD)\n\n";
            $prompt .= "Se algum campo não estiver presente, use null.";

            try {
                $extraction = $this->claudeService->sendMessage($prompt, []);

                // Tenta parsear JSON
                $data = json_decode($extraction, true);

                if ($data && isset($data['edict_number'])) {
                    // Cria o edital
                    $edital = Edict::create([
                        'company_id' => $companyId,
                        'edict_number' => $data['edict_number'],
                        'organ' => $data['organ'] ?? 'Não informado',
                        'object' => $data['object'] ?? 'Não informado',
                        'modality' => $data['modality'] ?? 'pregao',
                        'estimated_value' => $data['value'] ?? 0,
                        'opening_date' => $data['opening_date'] ?? now(),
                        'closing_date' => $data['closing_date'] ?? now()->addDays(30),
                        'status' => 'imported'
                    ]);

                    $text .= "✅ **Edital cadastrado com sucesso!**\n\n";
                    $text .= "**Número:** " . $edital->edict_number . "\n";
                    $text .= "**Órgão:** " . $edital->organ . "\n";
                    $text .= "**Objeto:** " . $edital->object . "\n";
                    $text .= "**Modalidade:** " . $edital->modality . "\n";
                    $text .= "**Valor Estimado:** R$ " . number_format($edital->estimated_value, 2, ',', '.') . "\n\n";
                    $text .= "O edital foi salvo no sistema e está disponível na lista de licitações.";

                    return ['text' => $text, 'data' => ['edital' => $edital]];
                }
            } catch (\Exception $e) {
                Log::error('Erro ao cadastrar edital via IA: ' . $e->getMessage());
            }
        }

        // Fallback: guia manual
        $text .= "⚠️ Para cadastrar um edital, forneça as seguintes informações:\n\n";
        $text .= "**Informações obrigatórias:**\n";
        $text .= "- Número do edital\n";
        $text .= "- Órgão responsável\n";
        $text .= "- Objeto da licitação\n";
        $text .= "- Modalidade (pregão, concorrência, etc)\n";
        $text .= "- Valor estimado\n";
        $text .= "- Data de abertura\n";
        $text .= "- Data de fechamento\n\n";
        $text .= "**Exemplo:**\n";
        $text .= "\"Cadastrar edital PE 001/2024 da Prefeitura Municipal, objeto: aquisição de computadores, modalidade pregão eletrônico, valor R\$ 150.000, abertura 01/11/2024, fechamento 30/11/2024\"";

        return ['text' => $text];
    }

    private function cadastrarFinanceiro($message, $companyId)
    {
        $messageLower = mb_strtolower($this->removeAccents($message), 'UTF-8');

        // Verifica se a mensagem é muito genérica (sem detalhes)
        $isGeneric = !preg_match('/\d/', $message) && // Não tem números (valor ou data)
                     (str_word_count($message) < 5); // Mensagem muito curta

        if ($isGeneric) {
            // Mensagem genérica - oferecer opções
            $text = "💰 **Cadastro de Transação Financeira**\n\n";
            $text .= "Você quer cadastrar uma receita ou despesa? Posso ajudá-lo de duas formas:\n\n";
            $text .= "**🤖 Opção 1: Cadastro Automático via IA**\n";
            $text .= "Me forneça todos os detalhes em uma mensagem e eu cadastro para você!\n\n";
            $text .= "**Exemplos:**\n";
            $text .= "• \"Registrar receita de R\$ 50.000 do contrato X em 15/10/2024\"\n";
            $text .= "• \"Lançar despesa de R\$ 3.500 com impostos em 20/10/2024\"\n";
            $text .= "• \"Criar receita R\$ 25.000 serviços prestados categoria contratos hoje\"\n\n";
            $text .= "**📚 Opção 2: Passo a Passo Manual**\n";
            $text .= "Digite: **\"Como cadastrar receita\"** ou **\"Como cadastrar despesa\"** para ver o tutorial completo.\n\n";
            $text .= "**💡 Dica:** Quanto mais detalhes você fornecer (valor, descrição, data), mais preciso será o cadastro automático!";

            return ['text' => $text];
        }

        // Mensagem com detalhes - tentar cadastro via IA
        $text = "💰 **Cadastro de Transação Financeira via IA**\n\n";

        // Usa Claude para extrair informações
        if ($this->claudeService->isConfigured()) {
            $prompt = "Extraia as seguintes informações desta solicitação de transação financeira:\n\n";
            $prompt .= "Mensagem: {$message}\n\n";
            $prompt .= "Retorne um JSON com os campos:\n";
            $prompt .= "- type (tipo: 'receita' ou 'despesa')\n";
            $prompt .= "- description (descrição da transação)\n";
            $prompt .= "- amount (valor em número, sem R\$ ou pontos/vírgulas)\n";
            $prompt .= "- category (categoria: contratos, servicos, impostos, etc)\n";
            $prompt .= "- date (data YYYY-MM-DD)\n\n";
            $prompt .= "Se algum campo não estiver presente, use null.";

            try {
                $extraction = $this->claudeService->sendMessage($prompt, []);

                // Tenta parsear JSON
                $data = json_decode($extraction, true);

                if ($data && isset($data['type']) && isset($data['amount'])) {
                    // Cria a transação
                    $transaction = FinancialTransaction::create([
                        'company_id' => $companyId,
                        'type' => $data['type'],
                        'description' => $data['description'] ?? 'Transação via EvolutIA',
                        'amount' => $data['amount'],
                        'category' => $data['category'] ?? 'outros',
                        'transaction_date' => $data['date'] ?? now()
                    ]);

                    $icon = $data['type'] === 'receita' ? '📈' : '📉';
                    $text .= "✅ **Transação cadastrada com sucesso!** {$icon}\n\n";
                    $text .= "**Tipo:** " . ucfirst($transaction->type) . "\n";
                    $text .= "**Descrição:** " . $transaction->description . "\n";
                    $text .= "**Valor:** R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
                    $text .= "**Categoria:** " . ucfirst($transaction->category) . "\n";
                    $text .= "**Data:** " . $transaction->transaction_date->format('d/m/Y') . "\n\n";
                    $text .= "A transação foi registrada no sistema financeiro.";

                    return ['text' => $text, 'data' => ['transaction' => $transaction]];
                }
            } catch (\Exception $e) {
                Log::error('Erro ao cadastrar transação via IA: ' . $e->getMessage());
            }
        }

        // Fallback: informações insuficientes
        $text .= "⚠️ **Informações insuficientes para cadastro automático**\n\n";
        $text .= "Para cadastrar automaticamente, forneça:\n\n";
        $text .= "**Informações obrigatórias:**\n";
        $text .= "✓ Tipo (receita ou despesa)\n";
        $text .= "✓ Descrição\n";
        $text .= "✓ Valor (em R$)\n";
        $text .= "✓ Data\n\n";
        $text .= "**Exemplo completo:**\n";
        $text .= "\"Registrar receita de R\$ 50.000 referente ao contrato X em 15/10/2024\"\n\n";
        $text .= "**Ou digite:**\n";
        $text .= "• **\"Como cadastrar receita\"** para ver o passo a passo manual\n";
        $text .= "• **\"Como cadastrar despesa\"** para tutorial de despesas";

        return ['text' => $text];
    }

    private function guiaCadastro($message)
    {
        $messageLower = mb_strtolower($this->removeAccents($message), 'UTF-8');

        $text = "📚 **Guia de Cadastro no EvolutionERP**\n\n";

        // Determina qual guia mostrar
        if (strpos($messageLower, 'edital') !== false || strpos($messageLower, 'licitacao') !== false) {
            $text .= "**📝 Como Cadastrar um Edital:**\n\n";
            $text .= "**Passo 1:** Acesse o menu \"Licitações\" > \"Editais\"\n\n";
            $text .= "**Passo 2:** Clique no botão \"+ Novo Edital\"\n\n";
            $text .= "**Passo 3:** Preencha as informações:\n";
            $text .= "- Número do Edital (ex: PE 001/2024)\n";
            $text .= "- Órgão (ex: Prefeitura Municipal)\n";
            $text .= "- Objeto (descrição do que será licitado)\n";
            $text .= "- Modalidade (Pregão, Concorrência, etc)\n";
            $text .= "- Valor Estimado\n";
            $text .= "- Data de Abertura\n";
            $text .= "- Data de Fechamento\n";
            $text .= "- Status (Importado, Analisado, etc)\n\n";
            $text .= "**Passo 4:** Anexe o arquivo do edital (PDF)\n\n";
            $text .= "**Passo 5:** Clique em \"Salvar\"\n\n";
            $text .= "💡 **Dica:** Você também pode me pedir para cadastrar dizendo: \"Cadastrar edital [informações]\"";

        } else if (strpos($messageLower, 'financ') !== false || strpos($messageLower, 'receita') !== false || strpos($messageLower, 'despesa') !== false) {
            $text .= "**💰 Como Cadastrar uma Transação Financeira:**\n\n";
            $text .= "**Passo 1:** Acesse o menu \"Financeiro\"\n\n";
            $text .= "**Passo 2:** Clique em \"+ Nova Transação\"\n\n";
            $text .= "**Passo 3:** Selecione o tipo:\n";
            $text .= "- 📈 Receita (entrada de dinheiro)\n";
            $text .= "- 📉 Despesa (saída de dinheiro)\n\n";
            $text .= "**Passo 4:** Preencha:\n";
            $text .= "- Descrição (ex: \"Pagamento Contrato X\")\n";
            $text .= "- Valor (ex: R\$ 50.000,00)\n";
            $text .= "- Categoria (Contratos, Impostos, Serviços, etc)\n";
            $text .= "- Data da transação\n\n";
            $text .= "**Passo 5:** Clique em \"Salvar\"\n\n";
            $text .= "💡 **Dica:** Você também pode me pedir: \"Registrar receita de R\$ [valor] em [data]\"";

        } else if (strpos($messageLower, 'documento') !== false) {
            $text .= "**📄 Como Cadastrar um Documento:**\n\n";
            $text .= "**Passo 1:** Acesse o menu \"Documentos\"\n\n";
            $text .= "**Passo 2:** Clique em \"+ Novo Documento\"\n\n";
            $text .= "**Passo 3:** Preencha:\n";
            $text .= "- Nome do Documento\n";
            $text .= "- Tipo (Certidão, Contrato, Alvará, etc)\n";
            $text .= "- Data de Emissão\n";
            $text .= "- Data de Validade (se aplicável)\n\n";
            $text .= "**Passo 4:** Faça upload do arquivo\n\n";
            $text .= "**Passo 5:** Clique em \"Salvar\"\n\n";
            $text .= "⏰ **Importante:** O sistema alerta quando documentos estão próximos do vencimento!";

        } else {
            // Guia geral
            $text .= "**Principais funcionalidades de cadastro:**\n\n";
            $text .= "1. **📝 Editais**\n";
            $text .= "   - Cadastre licitações e editais\n";
            $text .= "   - Acompanhe status e prazos\n\n";
            $text .= "2. **💰 Financeiro**\n";
            $text .= "   - Registre receitas e despesas\n";
            $text .= "   - Monitore saldo e fluxo de caixa\n\n";
            $text .= "3. **📄 Documentos**\n";
            $text .= "   - Gerencie documentos da empresa\n";
            $text .= "   - Receba alertas de vencimento\n\n";
            $text .= "4. **📊 Kanban**\n";
            $text .= "   - Organize processos visualmente\n";
            $text .= "   - Acompanhe progresso de tarefas\n\n";
            $text .= "**Precisa de ajuda específica?**\n";
            $text .= "Pergunte: \"Como cadastrar editais?\" ou \"Como cadastrar transações financeiras?\"";
        }

        return ['text' => $text];
    }

    private function explicarCapacidades()
    {
        $text = "✨ **Sim! Posso fazer diversos cadastros para você!**\n\n";
        $text .= "**📝 Cadastro de Editais:**\n";
        $text .= "Basta me fornecer as informações e eu cadastro automaticamente:\n";
        $text .= "- Número do edital\n";
        $text .= "- Órgão responsável\n";
        $text .= "- Objeto da licitação\n";
        $text .= "- Modalidade e valor estimado\n";
        $text .= "- Datas de abertura e fechamento\n\n";
        $text .= "**Exemplo:** \"Cadastrar edital PE 001/2024 da Prefeitura, objeto: computadores, valor R\$ 150.000\"\n\n";
        $text .= "**💰 Registro Financeiro:**\n";
        $text .= "Posso registrar receitas e despesas:\n";
        $text .= "- Tipo (receita ou despesa)\n";
        $text .= "- Valor e descrição\n";
        $text .= "- Data e categoria\n\n";
        $text .= "**Exemplo:** \"Registrar receita de R\$ 50.000 do contrato X em 15/10/2024\"\n\n";
        $text .= "**📚 Outras Capacidades:**\n";
        $text .= "- 📄 Analisar documentos enviados\n";
        $text .= "- 🔍 Buscar dados do PNCP\n";
        $text .= "- 💡 Ensinar a usar o sistema\n";
        $text .= "- 📊 Fornecer informações sobre licitações\n\n";
        $text .= "**O que você gostaria que eu fizesse?**";

        return ['text' => $text];
    }

    private function mostrarMenu()
    {
        $text = "🏠 **Menu Principal - EvolutIA**\n\n";
        $text .= "Olá! Sou a **EvolutIA**, sua assistente especializada em licitações públicas brasileiras! 🤖\n\n";
        $text .= "**📊 Informações do Sistema:**\n";
        $text .= "- \"Quais editais estão abertos?\"\n";
        $text .= "- \"Resumo financeiro\"\n";
        $text .= "- \"Documentos vencendo\"\n\n";
        $text .= "**🤖 Cadastros Inteligentes:**\n";
        $text .= "- \"Cadastrar edital [informações]\"\n";
        $text .= "- \"Registrar receita/despesa\"\n";
        $text .= "- \"Como cadastrar editais?\"\n\n";
        $text .= "**📚 Conhecimento sobre Licitações:**\n";
        $text .= "- \"Como funciona uma licitação?\"\n";
        $text .= "- \"Tipos de licitação\"\n";
        $text .= "- \"Prazos e documentação\"\n";
        $text .= "- \"Lei 14.133/2021\"\n\n";
        $text .= "**🌐 Dados Governamentais (PNCP):**\n";
        $text .= "- \"Buscar contratos PNCP\"\n";
        $text .= "- \"Avisos públicos\"\n";
        $text .= "- \"Estatísticas do PNCP\"\n\n";
        $text .= "**💡 Dicas:**\n";
        $text .= "- Use /menu a qualquer momento para voltar aqui\n";
        $text .= "- Faça perguntas naturalmente\n";
        $text .= "- Anexe documentos para análise\n\n";
        $text .= "**Como posso ajudá-lo hoje?**";

        return ['text' => $text];
    }

    private function respostaGeral($message)
    {
        $text = "Olá! Sou a **EvolutIA**, sua assistente especializada em licitações públicas brasileiras! 🤖\n\n";
        $text .= "Posso ajudá-lo com:\n\n";
        $text .= "**📊 Informações do Sistema:**\n";
        $text .= "- Editais cadastrados e em andamento\n";
        $text .= "- Resumo financeiro e transações\n";
        $text .= "- Documentos e vencimentos\n\n";
        $text .= "**🤖 Cadastros Inteligentes:**\n";
        $text .= "- Cadastrar editais via mensagem\n";
        $text .= "- Registrar transações financeiras\n";
        $text .= "- Guias passo a passo\n\n";
        $text .= "**📚 Conhecimento sobre Licitações:**\n";
        $text .= "- Como funciona uma licitação\n";
        $text .= "- Tipos e modalidades\n";
        $text .= "- Legislação (Lei 14.133/2021 e Lei 8.666/93)\n";
        $text .= "- Prazos e procedimentos\n\n";
        $text .= "**🌐 Dados Governamentais (PNCP):**\n";
        $text .= "- Contratos públicos em tempo real\n";
        $text .= "- Avisos e editais publicados\n";
        $text .= "- Estatísticas de contratações\n\n";
        $text .= "💡 **Dica:** Digite /menu para ver exemplos de perguntas!\n\n";
        $text .= "Como posso ajudá-lo hoje?";

        return ['text' => $text];
    }
}
