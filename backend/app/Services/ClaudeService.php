<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private $apiKey;
    private $apiUrl = 'https://api.anthropic.com/v1/messages';
    private $model = 'claude-3-5-sonnet-20241022';
    private $maxTokens = 2048;

    public function __construct()
    {
        $this->apiKey = env('ANTHROPIC_API_KEY');
    }

    /**
     * Verifica se a API está configurada
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Envia uma mensagem para o Claude e retorna a resposta
     */
    public function sendMessage(string $message, array $context = []): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('Claude API key not configured');
            return null;
        }

        try {
            $systemPrompt = $this->buildSystemPrompt($context);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post($this->apiUrl, [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? null;
            }

            Log::error('Claude API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Claude API exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Constrói o prompt do sistema com contexto
     */
    private function buildSystemPrompt(array $context): string
    {
        $prompt = "Você é a EvolutIA, uma assistente virtual especializada em licitações públicas brasileiras.\n\n";

        $prompt .= "**Suas características:**\n";
        $prompt .= "- Você é amigável, profissional e objetiva\n";
        $prompt .= "- Você domina completamente a legislação brasileira de licitações (Lei 14.133/2021, Lei 8.666/93, Lei 10.520/02)\n";
        $prompt .= "- Você fornece respostas práticas e aplicáveis\n";
        $prompt .= "- Você usa formatação Markdown para melhor legibilidade\n";
        $prompt .= "- Você é concisa mas completa nas respostas\n\n";

        $prompt .= "**Conhecimento especializado:**\n";
        $prompt .= "- Modalidades: Pregão, Concorrência, Concurso, Leilão, Diálogo Competitivo\n";
        $prompt .= "- Tipos de julgamento: Menor Preço, Melhor Técnica, Técnica e Preço\n";
        $prompt .= "- Fases: Edital, Habilitação, Proposta, Adjudicação, Homologação\n";
        $prompt .= "- Recursos administrativos e prazos\n";
        $prompt .= "- Documentação de habilitação\n";
        $prompt .= "- Sistema de Registro de Preços (SRP)\n\n";

        // Adiciona contexto do sistema se disponível
        if (!empty($context['editais_count'])) {
            $prompt .= "**Contexto do sistema atual:**\n";
            $prompt .= "- Total de editais cadastrados: {$context['editais_count']}\n";
        }

        if (!empty($context['saldo_financeiro'])) {
            $prompt .= "- Saldo financeiro: R$ " . number_format($context['saldo_financeiro'], 2, ',', '.') . "\n";
        }

        if (!empty($context['documentos_vencendo'])) {
            $prompt .= "- Documentos vencendo em 30 dias: {$context['documentos_vencendo']}\n";
        }

        $prompt .= "\n**Diretrizes:**\n";
        $prompt .= "- Sempre cite a legislação quando relevante\n";
        $prompt .= "- Use exemplos práticos quando possível\n";
        $prompt .= "- Se não souber algo, seja honesta e sugira onde buscar a informação\n";
        $prompt .= "- Mantenha respostas com no máximo 300 palavras, exceto se solicitado detalhamento\n";
        $prompt .= "- Use emojis moderadamente para tornar a conversa mais amigável\n";

        return $prompt;
    }

    /**
     * Analisa um edital e retorna insights
     */
    public function analyzeEdict(array $edictData): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $message = "Analise este edital e forneça insights práticos:\n\n";
        $message .= "**Número:** {$edictData['edict_number']}\n";
        $message .= "**Órgão:** {$edictData['organ']}\n";

        if (!empty($edictData['description'])) {
            $message .= "**Descrição:** {$edictData['description']}\n";
        }

        if (!empty($edictData['estimated_value'])) {
            $message .= "**Valor estimado:** R$ " . number_format($edictData['estimated_value'], 2, ',', '.') . "\n";
        }

        $message .= "\nForneça:\n";
        $message .= "1. Principais pontos de atenção\n";
        $message .= "2. Documentação provável necessária\n";
        $message .= "3. Recomendações estratégicas\n";

        return $this->sendMessage($message);
    }

    /**
     * Gera sugestão de resposta com base no histórico
     */
    public function suggestResponse(array $conversationHistory): ?string
    {
        if (!$this->isConfigured() || empty($conversationHistory)) {
            return null;
        }

        $context = "Histórico da conversa:\n\n";
        foreach ($conversationHistory as $msg) {
            $role = $msg['role'] === 'user' ? 'Usuário' : 'EvolutIA';
            $context .= "**{$role}:** {$msg['content']}\n\n";
        }

        $message = $context . "Com base nesta conversa, sugira 3 perguntas relevantes que o usuário pode fazer a seguir.";

        return $this->sendMessage($message);
    }
}
