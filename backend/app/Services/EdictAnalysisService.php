<?php

namespace App\Services;

use App\Models\Edict;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EdictAnalysisService
{
    protected $documentService;

    public function __construct(DocumentExtractionService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Analisa um edital completo
     */
    public function analyzeEdict(Edict $edict, string $filePath): array
    {
        try {
            // 1. Extrair texto do documento (PDF, Word, Excel, etc)
            $text = $this->documentService->extractText($filePath);

            if (empty($text)) {
                throw new Exception("Não foi possível extrair texto do documento");
            }

            // 2. Analisar com IA (Claude)
            $analysis = $this->analyzeWithClaude($text);

            // 3. Buscar documentos da empresa
            $companyDocs = CompanyDocument::where('company_id', $edict->company_id)
                ->where('status', 'valid')
                ->get();

            // 4. Verificar compliance
            $compliance = $this->checkCompliance(
                $analysis['requirements'] ?? [],
                $companyDocs
            );

            // 5. Calcular custos
            $costs = $this->calculateCosts($analysis);

            // 6. Gerar recomendação
            $recommendation = $this->generateRecommendation($compliance, $costs, $analysis);

            return array_merge($analysis, $compliance, $costs, $recommendation);

        } catch (Exception $e) {
            Log::error('Erro na análise do edital: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analisa o texto do edital usando Claude API
     */
    protected function analyzeWithClaude(string $text): array
    {
        $apiKey = env('ANTHROPIC_API_KEY');

        // Se não tiver API key, usar análise simulada
        if (empty($apiKey)) {
            return $this->simulatedAnalysis($text);
        }

        try {
            $prompt = $this->buildAnalysisPrompt($text);

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 4096,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

            if (!$response->successful()) {
                throw new Exception('Erro na API Claude: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['content'][0]['text'] ?? '{}';

            // Extrair JSON do conteúdo
            $jsonMatch = preg_match('/\{[\s\S]*\}/', $content, $matches);
            $jsonData = $jsonMatch ? $matches[0] : '{}';

            $analysis = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Resposta da IA não é um JSON válido');
            }

            return $this->normalizeAnalysis($analysis);

        } catch (Exception $e) {
            Log::error('Erro na API Claude: ' . $e->getMessage());
            return $this->simulatedAnalysis($text);
        }
    }

    /**
     * Constrói o prompt para análise
     */
    protected function buildAnalysisPrompt(string $text): string
    {
        return "Você é um especialista em análise de editais de licitações públicas brasileiras, com profundo conhecimento da Lei 8.666/93, Lei 10.520/02 e Lei 14.133/21 (Nova Lei de Licitações).

INSTRUÇÕES CRÍTICAS GERAIS:
1. Analise TODO o documento cuidadosamente, do início ao fim - leia TODAS as páginas
2. Extraia informações EXATAS do edital (não invente dados)
3. Para datas, procure por seções como \"Cronograma\", \"Prazos\", \"Sessão Pública\"
4. Para valores, procure por \"Valor Estimado\", \"Preço Máximo\", \"Orçamento\"
5. Para documentos, procure seção de \"Habilitação\", \"Documentação Exigida\", \"Qualificação\"

INSTRUÇÕES ESPECÍFICAS PARA DADOS CRÍTICOS (EXTREMAMENTE IMPORTANTES):

**NÚMERO DO EDITAL (CAMPO CRÍTICO #1):**
ESTE É O CAMPO MAIS IMPORTANTE - LEIA COM MÁXIMA ATENÇÃO!

Localização (procure na seguinte ordem):
1. PRIMEIRA LINHA ou TÍTULO do documento (90% dos casos)
2. Cabeçalho ou timbre oficial
3. Primeira seção após o logotipo
4. Preâmbulo (parágrafo introdutório)

Formatos mais comuns (capture EXATAMENTE como aparece):
- \"Edital nº 001/2025\" → extraia: \"001/2025\"
- \"EDITAL Nº 123/2025-PMC\" → extraia: \"123/2025-PMC\"
- \"PREGÃO ELETRÔNICO Nº 034/2025\" → extraia: \"034/2025\"
- \"PE 045/2025-SRP\" → extraia: \"045/2025-SRP\"
- \"Edital de Licitação 0067/2025\" → extraia: \"0067/2025\"
- \"CONCORRÊNCIA PÚBLICA Nº 012/2025\" → extraia: \"012/2025\"
- \"Edital n° 001-2025\" → extraia: \"001-2025\"
- \"AVISO DE LICITAÇÃO Nº 789/2025\" → extraia: \"789/2025\"

Padrões de busca (palavras que precedem o número):
- \"Edital\"
- \"Pregão\"
- \"Concorrência\"
- \"Tomada de Preços\"
- \"Convite\"
- \"Aviso de Licitação\"
- Seguidos de: \"nº\", \"n°\", \"n º\", \"número\", \":\"

REGRAS ABSOLUTAS:
✓ TODO edital TEM um número - NUNCA retorne null
✓ Se encontrar \"PREGÃO ELETRÔNICO Nº 034/2025\", extraia APENAS \"034/2025\" (sem a modalidade)
✓ Mantenha o formato original (barras, hífens, letras)
✓ Se encontrar múltiplos números, escolha o primeiro (é o número do edital)
✓ Números típicos: 001/2025, 123/2025, PE-045/2025, 0067-2025-PMC

ERRO MAIS COMUM: Confundir com número do processo
- Número do Edital: curto, formato XX/YYYY ou XXX/YYYY
- Número do Processo: longo, formato XX.XXXX.XXX/YYYY-XX

**NÚMERO DO PROCESSO (CAMPO CRÍTICO #2):**
ATENÇÃO: Este é DIFERENTE do número do edital!

Características que diferenciam:
- Número do Processo é LONGO e com PONTOS/BARRAS
- Número do Edital é CURTO (só XXX/YYYY)
- O processo SEMPRE existe, mas pode estar \"escondido\" no texto

Localização (procure em TODAS estas áreas):
1. Logo após o cabeçalho/timbre (muito comum)
2. Primeira página, em destaque ou em tabela
3. Seção \"PREÂMBULO\" ou \"IDENTIFICAÇÃO\"
4. Antes ou depois da modalidade
5. Em rodapés ou notas de protocolo
6. Próximo a \"Interessado:\", \"Órgão:\", \"Tipo:\"

Formatos EXATOS encontrados em editais reais:
Federal/Judicial:
- \"23106.001234/2025-78\" (padrão federal)
- \"17944.000456/2025-11\"
- \"0001234-56.2025.8.26.0000\" (padrão judiciário)
- \"50603.000345/2025-49\"

Estadual/Municipal:
- \"2025.15.0001.01.01/2025-01\"
- \"2025/001-PREF\"
- \"12345/2025\"
- \"PA 2025.01.0123\"
- \"2025.0001.LIC\"

Palavras-chave ANTES do número (procure por estas exatas):
- \"Processo:\"
- \"Processo Administrativo:\"
- \"Processo Administrativo nº\"
- \"Processo Licitatório:\"
- \"PA:\"
- \"PA nº\"
- \"Proc.:\"
- \"Processo n°\"
- \"Processo SEI\"
- \"Processo E-DOCS\"
- \"NUP:\" (Número Único de Protocolo)

EXEMPLOS PRÁTICOS de como aparece no texto:
✓ \"Processo Administrativo nº 23106.001234/2025-78\"
✓ \"Interessado: PREFEITURA / Processo: 2025/001-PREF\"
✓ \"PA: 12345/2025\"
✓ \"Processo SEI nº 50603.000345/2025-49\"
✓ \"NUP 17944.000456/2025-11\"

REGRAS IMPORTANTES:
✓ Se encontrar MÚLTIPLOS processos, escolha o PRIMEIRO mencionado no cabeçalho
✓ NÃO confunda com CNPJ (14 dígitos sem barras)
✓ NÃO confunda com número do edital (muito menor)
✓ Mantenha TODOS os pontos, barras e hífens do formato original
✓ Se realmente não encontrar após procurar em TODO o documento, retorne null
✓ NUNCA invente - se tiver dúvida, retorne null

**UASG (CAMPO CRÍTICO #3 - Apenas Licitações Federais):**
ATENÇÃO: UASG existe SOMENTE em licitações do GOVERNO FEDERAL!

O que é UASG:
- Código de EXATAMENTE 6 dígitos (nem mais, nem menos)
- Identifica órgãos federais no sistema Comprasnet/PNCP
- Se o órgão for Prefeitura, Estado, Município → NÃO tem UASG

Como identificar se o edital é FEDERAL (e pode ter UASG):
✓ Ministérios (Ministério da Saúde, Defesa, Educação...)
✓ Autarquias Federais (INSS, IBAMA, ANATEL...)
✓ Universidades Federais (UFRJ, USP, UFMG...)
✓ Empresas Públicas Federais (Correios, Caixa...)
✓ Forças Armadas (Exército, Marinha, Aeronáutica)

Se o edital for destes, NÃO procure UASG (retorne null):
✗ Prefeituras Municipais
✗ Câmaras Municipais
✗ Secretarias Municipais
✗ Governos Estaduais
✗ Secretarias Estaduais
✗ Empresas privadas

Localização do código UASG:
1. Cabeçalho ou topo do edital (lado direito geralmente)
2. Seção de identificação ou dados da unidade
3. Próximo ao CNPJ do órgão
4. Em tabelas de \"Dados da Licitação\"
5. Portal: geralmente aparece \"Portal Comprasnet - UASG 123456\"

Palavras-chave que precedem o código:
- \"UASG:\"
- \"UASG Nº\"
- \"Código UASG:\"
- \"Unidade Gestora:\"
- \"UG:\"
- \"Código da Unidade:\"

EXEMPLOS REAIS de como aparece:
✓ \"UASG: 110244\" → extraia: \"110244\"
✓ \"Código UASG 160010\" → extraia: \"160010\"
✓ \"UG: 926190\" → extraia: \"926190\"
✓ \"Unidade Gestora 153173\" → extraia: \"153173\"
✓ \"UASG Nº 170020\" → extraia: \"170020\"

VALIDAÇÃO RIGOROSA:
✓ Deve ter EXATAMENTE 6 dígitos (123456)
✗ NÃO pode ter 5 dígitos (12345) - não é UASG
✗ NÃO pode ter 7+ dígitos (1234567) - não é UASG
✗ NÃO pode ter letras (12A456) - não é UASG
✗ NÃO confundir com CNPJ (14 dígitos: 12.345.678/0001-90)
✗ NÃO confundir com código SIAFI (7 dígitos)
✗ NÃO confundir com telefone
✗ NÃO confundir com CEP (8 dígitos: 12345-678)

REGRA DE OURO:
- Se o órgão for MUNICIPAL ou ESTADUAL → retorne null imediatamente
- Se for FEDERAL mas não encontrar \"UASG\" no texto → retorne null
- NUNCA invente um código UASG
- Quando em dúvida → null

**NOME DO ÓRGÃO:**
- EXTREMAMENTE IMPORTANTE - TODO edital tem um órgão licitante
- Procure no CABEÇALHO, primeira página, preâmbulo
- Use o nome COMPLETO e OFICIAL do órgão
- Formatos corretos:
  * \"Prefeitura Municipal de [Cidade]\"
  * \"Ministério da [Área]\"
  * \"Universidade Federal de [Estado]\"
  * \"Secretaria [Tipo] do Estado de [Estado]\"
  * \"Tribunal [Tipo] do [Local]\"
- NÃO abreviar (usar \"Prefeitura Municipal de São Paulo\" e NÃO \"PMSP\" ou \"PM\")
- NÃO usar apenas o departamento (ex: \"Secretaria de Saúde\" sem mencionar prefeitura/estado)
- Procure por: \"Órgão Licitante:\", \"Entidade:\", \"Contratante:\", cabeçalho oficial
- Se houver múltiplos órgãos, use o principal/licitante (não o órgão solicitante secundário)
- NUNCA deixe vazio

INSTRUÇÕES ESPECÍFICAS PARA EXTRAÇÃO DO OBJETO:

O OBJETO DA LICITAÇÃO é a informação MAIS IMPORTANTE do edital. Siga estas regras:

1. LOCALIZAÇÃO DO OBJETO:
   - Procure por seções com títulos: \"OBJETO\", \"DO OBJETO\", \"OBJETO DA LICITAÇÃO\", \"1. OBJETO\", \"ITEM 1\"
   - O objeto geralmente está nas primeiras páginas do edital (páginas 1-5)
   - Pode estar após o cabeçalho/preâmbulo e antes dos demais itens

2. IDENTIFICAÇÃO CORRETA:
   - O objeto descreve O QUE será contratado (serviço, obra, produto)
   - NÃO confunda com: número do edital, órgão, modalidade, justificativa
   - O objeto geralmente começa com verbos como: \"Contratação de\", \"Aquisição de\", \"Prestação de\", \"Fornecimento de\"

3. EXTRAÇÃO E RESUMO:
   - Leia TODO o texto da seção do objeto (pode ter vários parágrafos)
   - Extraia a ESSÊNCIA: o que será fornecido/prestado e principais características
   - RESUMA em 1-3 frases, mantendo informações-chave:
     * Tipo de serviço/produto
     * Quantidade (se relevante)
     * Especificações principais
     * Local/destino (se importante)
   - Máximo de 300 caracteres para o resumo
   - Use linguagem clara e direta
   - Preserve termos técnicos importantes

4. EXEMPLOS DE BOA EXTRAÇÃO:

Texto original: \"Contratação de empresa especializada para prestação de serviços de limpeza e conservação predial, com fornecimento de mão de obra, materiais e equipamentos, nas dependências da Prefeitura Municipal, conforme especificações do Termo de Referência anexo.\"

Resumo CORRETO: \"Prestação de serviços de limpeza e conservação predial com fornecimento de mão de obra, materiais e equipamentos para a Prefeitura Municipal.\"

Texto original: \"Aquisição de equipamentos de informática, compreendendo computadores desktop tipo 1 (50 unidades), computadores desktop tipo 2 (30 unidades), notebooks (20 unidades), monitores LED 24 polegadas (80 unidades) e impressoras laser (15 unidades), conforme especificações técnicas detalhadas no Anexo I.\"

Resumo CORRETO: \"Aquisição de 80 computadores (desktop e notebooks), 80 monitores LED 24\", e 15 impressoras laser conforme especificações técnicas.\"

5. ERROS COMUNS A EVITAR:
   ✗ NÃO copie apenas o número do edital ou processo
   ✗ NÃO inclua texto genérico como \"conforme termo de referência\"
   ✗ NÃO coloque informações de prazos ou datas
   ✗ NÃO inclua valores monetários
   ✗ NÃO copie textos de outras seções (como justificativa)
   ✗ NÃO deixe o campo vazio se houver objeto no edital

Analise o edital de licitação pública brasileira e extraia as informações em formato JSON puro:

{
  \"edict_number\": \"NÚMERO DO EDITAL - OBRIGATÓRIO - procure no topo/cabeçalho (ex: '001/2025', 'PE 034/2025', '123/2025-PREF'). NUNCA deixe null.\",
  \"uasg_number\": \"código UASG de 6 dígitos se for licitação FEDERAL (ex: '110244') ou null se não encontrar ou se for municipal/estadual\",
  \"process_number\": \"número completo do processo administrativo se encontrar (ex: '23106.123456/2025-78', '2025/001-PREF') ou null\",
  \"modality\": \"modalidade exata (Pregão Eletrônico, Pregão Presencial, Concorrência, Tomada de Preços, Convite, Dispensa, Inexigibilidade)\",
  \"organ\": \"nome COMPLETO e OFICIAL do órgão licitante - OBRIGATÓRIO (ex: 'Prefeitura Municipal de São Paulo', não 'PMSP'). NUNCA deixe null.\",
  \"object_description\": \"RESUMO CLARO E DIRETO do objeto em até 300 caracteres. Descreva O QUE será contratado (serviço/produto), principais especificações e quantidades relevantes. Ex: 'Prestação de serviços de limpeza predial com mão de obra e materiais para 3 prédios.'\",
  \"estimated_value\": valor total estimado em reais (apenas números, sem vírgulas ou pontos - ex: 150000.50),
  \"opening_date\": \"data de abertura/início do certame YYYY-MM-DD\",
  \"proposal_deadline\": \"prazo FINAL para envio de propostas YYYY-MM-DD HH:MM:SS\",
  \"session_date\": \"data e hora da sessão pública de disputa/julgamento YYYY-MM-DD HH:MM:SS\",
  \"publication_date\": \"data de publicação do edital YYYY-MM-DD\",
  \"closing_date\": \"data limite para questionamentos ou impugnações YYYY-MM-DD\",
  \"requirements\": [\"liste TODOS os requisitos técnicos, econômicos e jurídicos exigidos\"],
  \"required_documents\": [
    \"CNPJ\",
    \"Certidão Negativa de Débitos Federais (CND)\",
    \"Certidão Negativa de Débitos Estaduais\",
    \"Certidão Negativa de Débitos Municipais\",
    \"Certidão Negativa de Débitos Trabalhistas (CNDT)\",
    \"Certidão de Regularidade do FGTS (CRF)\",
    \"Prova de Inscrição Estadual\",
    \"Prova de Inscrição Municipal\",
    \"Balanço Patrimonial\",
    \"Certidão Negativa de Falência\",
    \"Atestado de Capacidade Técnica\",
    \"Registro/Inscrição Profissional\",
    \"liste EXATAMENTE todos os documentos mencionados na seção de HABILITAÇÃO\"
  ],
  \"bidding_portal_url\": \"URL do portal de compras (Comprasnet, PNCP, BNC, etc)\",
  \"unit_value\": valor unitário se for licitação por item (número),
  \"category\": \"classifique em: Tecnologia, Serviços, Obras, Fornecimento, Consultoria, Saúde, Educação, Segurança, Infraestrutura, Outros\"
}

REGRAS ESTRITAS:
- Retorne APENAS o JSON válido, sem ```json, sem comentários, sem texto extra
- Use null para informações não encontradas (NÃO invente dados)
- Datas sempre em formato brasileiro convertido para ISO (DD/MM/YYYY -> YYYY-MM-DD)
- Horários no formato 24h (ex: 14:30:00)
- Valores monetários: apenas números decimais (ex: 250000.00)
- Para required_documents: liste TODOS os documentos mencionados literalmente no edital
- Se o edital mencionar \"Conforme Lei 8.666\" ou similar, liste os documentos padrão da lei
- Seja PRECISO: copie números, datas e descrições EXATAMENTE como aparecem

TEXTO DO EDITAL:

" . substr($text, 0, 100000); // Limitar a 100k caracteres
    }

    /**
     * Análise simulada (quando não há API key ou falha)
     */
    protected function simulatedAnalysis(string $text): array
    {
        // Tentar extrair informações básicas com regex aprimorado
        $uasg = null;
        $process = null;
        $edictNumber = null;
        $organ = 'Órgão Público';

        // Extrair UASG (EXATAMENTE 6 dígitos)
        if (preg_match('/(?:UASG|UG|Unidade\s+Gestora|Código\s+UASG)[:\s]*([\d]{6})\b/i', $text, $matches)) {
            $uasg = $matches[1];
        }

        // Extrair número do processo (múltiplos padrões)
        $processPatterns = [
            '/(?:Processo|PA|Proc\.|NUP)[:\s]*([0-9]{5}\.[0-9]{6}\/[0-9]{4}-[0-9]{2})/i', // Federal: 23106.001234/2025-78
            '/(?:Processo|PA)[:\s]*([0-9]{4,5}\/[0-9]{4})/i', // Municipal: 12345/2025
            '/(?:Processo|PA)[:\s]*([0-9]{4}\.[0-9]{2}\.[0-9]{4}\/[0-9]{4}-[0-9]{2})/i', // Estadual
            '/(?:Processo|PA)[:\s]*([0-9]{7}-[0-9]{2}\.[0-9]{4}\.[0-9]\.[0-9]{2}\.[0-9]{4})/i', // Judiciário
        ];

        foreach ($processPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $process = $matches[1];
                break;
            }
        }

        // Extrair número do edital (múltiplos padrões, do mais específico ao mais genérico)
        $edictPatterns = [
            '/(?:PREGÃO\s+ELETRÔNICO|PE|Pregão\s+Eletrônico)\s*(?:nº|n º|n°|Nº|N°)?\s*([0-9]{1,4}\/[0-9]{4}(?:-[A-Z]+)?)/i',
            '/(?:EDITAL|Edital)\s*(?:nº|n º|n°|Nº|N°)?\s*([0-9]{1,4}\/[0-9]{4}(?:-[A-Z]+)?)/i',
            '/(?:CONCORRÊNCIA|Concorrência)\s*(?:nº|n º|n°|Nº)?\s*([0-9]{1,4}\/[0-9]{4})/i',
            '/(?:TOMADA\s+DE\s+PREÇOS)\s*(?:nº|n º|n°|Nº)?\s*([0-9]{1,4}\/[0-9]{4})/i',
        ];

        foreach ($edictPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $edictNumber = $matches[1];
                break;
            }
        }

        // Se não encontrou ainda, tentar padrão genérico
        if (!$edictNumber && preg_match('/\b([0-9]{1,4}\/[0-9]{4})\b/', $text, $matches)) {
            $edictNumber = $matches[1];
        }

        // Extrair órgão (padrões mais específicos)
        $organPatterns = [
            '/(Prefeitura\s+Municipal\s+de\s+[A-ZÀ-Ú][a-zà-ú\s]+)/i',
            '/(Ministério\s+d[aeo]\s+[A-ZÀ-Ú][a-zà-ú\s]+)/i',
            '/(Secretaria\s+[A-ZÀ-Ú][a-zà-ú\s]+(?:do\s+Estado\s+de\s+[A-ZÀ-Ú][a-zà-ú]+)?)/i',
            '/(Universidade\s+Federal\s+de\s+[A-ZÀ-Ú][a-zà-ú]+)/i',
            '/(Governo\s+do\s+Estado\s+de\s+[A-ZÀ-Ú][a-zà-ú]+)/i',
        ];

        foreach ($organPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $organ = trim($matches[1]);
                break;
            }
        }

        return [
            'edict_number' => $edictNumber,
            'uasg_number' => $uasg,
            'process_number' => $process,
            'modality' => 'Pregão Eletrônico',
            'organ' => $organ,
            'object_description' => substr($text, 0, 500),
            'estimated_value' => 100000.00,
            'opening_date' => now()->addDays(15)->format('Y-m-d'),
            'proposal_deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'session_date' => now()->addDays(15)->format('Y-m-d H:i:s'),
            'requirements' => [
                'Regularidade Fiscal',
                'Certidões Negativas',
                'Qualificação Técnica',
            ],
            'required_documents' => [
                'CNPJ',
                'Certidão Federal',
                'Certidão Estadual',
                'Certidão Municipal',
            ],
            'bidding_portal_url' => 'https://www.gov.br/compras',
            'unit_value' => null,
            'category' => 'Serviços',
        ];
    }

    /**
     * Normaliza a análise
     */
    protected function normalizeAnalysis(array $analysis): array
    {
        // Processar documentos requeridos
        $requiredDocs = $analysis['required_documents'] ?? [];

        // Limpar e validar object_description
        $objectDescription = $analysis['object_description'] ?? null;
        if ($objectDescription) {
            // Limpar texto
            $objectDescription = trim($objectDescription);

            // Remover frases genéricas desnecessárias
            $genericPhrases = [
                'conforme especificações do termo de referência',
                'conforme termo de referência',
                'conforme anexo',
                'conforme edital',
                'de acordo com as especificações',
                'segundo as normas',
                'nos termos do edital',
            ];

            foreach ($genericPhrases as $phrase) {
                $objectDescription = preg_replace('/' . preg_quote($phrase, '/') . '/i', '', $objectDescription);
            }

            // Limpar espaços duplos
            $objectDescription = preg_replace('/\s+/', ' ', $objectDescription);
            $objectDescription = trim($objectDescription);

            // Limitar a 500 caracteres para segurança
            if (strlen($objectDescription) > 500) {
                $objectDescription = substr($objectDescription, 0, 497) . '...';
            }

            // Validar que não é apenas número ou muito curto
            if (strlen($objectDescription) < 20 || preg_match('/^\d+$/', $objectDescription)) {
                $objectDescription = null;
            }
        }

        // Extrair e limpar número do edital
        $edictNumber = $analysis['edict_number'] ?? null;
        if ($edictNumber) {
            $edictNumber = trim($edictNumber);

            // Se veio com "Edital", "Pregão", etc na frente, remover
            $edictNumber = preg_replace('/^(Edital|EDITAL|Pregão|PREGÃO|PE|Concorrência|CONCORRÊNCIA)\s*(nº|n º|n°|Nº|N°|número)?\s*/i', '', $edictNumber);
            $edictNumber = trim($edictNumber);

            // Se ficou vazio após limpeza, tentar manter original
            if (empty($edictNumber)) {
                $edictNumber = $analysis['edict_number'];
            }
        }

        // Validar número do processo (deve ter formato específico ou null)
        $processNumber = $analysis['process_number'] ?? null;
        if ($processNumber) {
            $processNumber = trim($processNumber);
            // Se for muito curto (menos de 4 caracteres), provavelmente não é válido
            if (strlen($processNumber) < 4) {
                $processNumber = null;
            }
        }

        // Validar UASG (deve ser exatamente 6 dígitos ou null)
        $uasgNumber = $analysis['uasg_number'] ?? null;
        if ($uasgNumber) {
            $uasgNumber = trim($uasgNumber);
            // Validar que tem exatamente 6 dígitos
            if (!preg_match('/^\d{6}$/', $uasgNumber)) {
                Log::warning('UASG inválido detectado (não tem 6 dígitos): ' . $uasgNumber);
                $uasgNumber = null;
            }
        }

        return [
            'edict_number' => $edictNumber,
            'uasg_number' => $uasgNumber,
            'process_number' => $processNumber,
            'modality' => $analysis['modality'] ?? null,
            'organ' => $analysis['organ'] ?? null,
            'object_description' => $objectDescription,
            'estimated_value' => (float) ($analysis['estimated_value'] ?? 0),
            'opening_date' => $analysis['opening_date'] ?? null,
            'proposal_deadline' => $analysis['proposal_deadline'] ?? null,
            'session_date' => $analysis['session_date'] ?? null,
            'publication_date' => $analysis['publication_date'] ?? null,
            'closing_date' => $analysis['closing_date'] ?? null,
            'requirements' => $analysis['requirements'] ?? [],
            'required_documents' => $requiredDocs,
            'bidding_portal_url' => $analysis['bidding_portal_url'] ?? null,
            'unit_value' => $analysis['unit_value'] ?? null,
            'category' => $analysis['category'] ?? 'Outros',
        ];
    }

    /**
     * Verifica compliance da empresa com os requisitos
     */
    protected function checkCompliance(array $requirements, $companyDocs): array
    {
        // Documentos disponíveis da empresa
        $companyDocTypes = $companyDocs->pluck('document_type')->toArray();

        // Mapear documentos comuns de licitações
        $docMapping = [
            // Variações de como os documentos aparecem nos editais -> tipo no sistema
            'cnpj' => 'cnpj',
            'cadastro nacional' => 'cnpj',
            'pessoa jurídica' => 'cnpj',

            'certidão federal' => 'certidao_federal',
            'certidão negativa federal' => 'certidao_federal',
            'cnd federal' => 'certidao_federal',
            'receita federal' => 'certidao_federal',
            'débitos federais' => 'certidao_federal',

            'certidão estadual' => 'certidao_estadual',
            'certidão negativa estadual' => 'certidao_estadual',
            'cnd estadual' => 'certidao_estadual',
            'débitos estaduais' => 'certidao_estadual',
            'fazenda estadual' => 'certidao_estadual',

            'certidão municipal' => 'certidao_municipal',
            'certidão negativa municipal' => 'certidao_municipal',
            'cnd municipal' => 'certidao_municipal',
            'débitos municipais' => 'certidao_municipal',
            'fazenda municipal' => 'certidao_municipal',
            'tributos municipais' => 'certidao_municipal',

            'fgts' => 'certidao_fgts',
            'certidão fgts' => 'certidao_fgts',
            'regularidade fgts' => 'certidao_fgts',
            'crf' => 'certidao_fgts',
            'fundo de garantia' => 'certidao_fgts',

            'certidão trabalhista' => 'certidao_trabalhista',
            'cndt' => 'certidao_trabalhista',
            'débitos trabalhistas' => 'certidao_trabalhista',
            'justiça do trabalho' => 'certidao_trabalhista',

            'balanço' => 'balanco_patrimonial',
            'balanço patrimonial' => 'balanco_patrimonial',
            'demonstrações financeiras' => 'balanco_patrimonial',
            'demonstrações contábeis' => 'balanco_patrimonial',

            'falência' => 'certidao_falencia',
            'concordata' => 'certidao_falencia',
            'recuperação judicial' => 'certidao_falencia',

            'atestado' => 'atestado_capacidade',
            'capacidade técnica' => 'atestado_capacidade',
            'comprovação técnica' => 'atestado_capacidade',
            'qualificação técnica' => 'atestado_capacidade',

            'registro profissional' => 'registro_profissional',
            'crea' => 'registro_profissional',
            'cau' => 'registro_profissional',
            'crc' => 'registro_profissional',
            'crm' => 'registro_profissional',

            'alvará' => 'alvara_funcionamento',
            'licença' => 'alvara_funcionamento',
            'funcionamento' => 'alvara_funcionamento',

            'contrato social' => 'contrato_social',
            'estatuto' => 'contrato_social',
            'ata' => 'contrato_social',
        ];

        // Nomes legíveis dos documentos
        $docNames = [
            'cnpj' => 'CNPJ',
            'certidao_federal' => 'Certidão Negativa de Débitos Federais',
            'certidao_estadual' => 'Certidão Negativa de Débitos Estaduais',
            'certidao_municipal' => 'Certidão Negativa de Débitos Municipais',
            'certidao_fgts' => 'Certidão de Regularidade do FGTS',
            'certidao_trabalhista' => 'Certidão Negativa de Débitos Trabalhistas',
            'balanco_patrimonial' => 'Balanço Patrimonial',
            'certidao_falencia' => 'Certidão Negativa de Falência',
            'atestado_capacidade' => 'Atestado de Capacidade Técnica',
            'registro_profissional' => 'Registro/Inscrição Profissional',
            'alvara_funcionamento' => 'Alvará de Funcionamento',
            'contrato_social' => 'Contrato Social/Estatuto',
        ];

        // Identificar documentos requeridos pelo edital
        $requiredDocTypes = [];
        foreach ($requirements as $requirement) {
            $reqLower = strtolower($requirement);
            foreach ($docMapping as $keyword => $type) {
                if (strpos($reqLower, $keyword) !== false) {
                    if (!in_array($type, $requiredDocTypes)) {
                        $requiredDocTypes[] = $type;
                    }
                }
            }
        }

        // Se não encontrou nenhum documento específico, usar lista padrão mínima
        if (empty($requiredDocTypes)) {
            $requiredDocTypes = [
                'cnpj',
                'certidao_federal',
                'certidao_estadual',
                'certidao_municipal',
                'certidao_fgts',
                'certidao_trabalhista',
            ];
        }

        $availableDocs = [];
        $missingDocs = [];

        foreach ($requiredDocTypes as $type) {
            $docName = $docNames[$type] ?? ucfirst(str_replace('_', ' ', $type));

            if (in_array($type, $companyDocTypes)) {
                $availableDocs[] = [
                    'type' => $type,
                    'name' => $docName,
                    'status' => 'available'
                ];
            } else {
                $missingDocs[] = [
                    'type' => $type,
                    'name' => $docName,
                    'status' => 'missing'
                ];
            }
        }

        $totalDocs = count($requiredDocTypes);
        $compliancePercentage = $totalDocs > 0 ? (count($availableDocs) / $totalDocs * 100) : 0;

        return [
            'company_compliance' => [
                'available' => $availableDocs,
                'missing' => $missingDocs,
                'percentage' => round($compliancePercentage, 2),
            ],
            'available_documents' => $availableDocs,
            'missing_requirements' => $missingDocs,
        ];
    }

    /**
     * Calcula custos estimados
     */
    protected function calculateCosts(array $analysis): array
    {
        $estimatedValue = $analysis['estimated_value'] ?? 0;

        if ($estimatedValue <= 0) {
            return [
                'tax_cost' => 0,
                'labor_cost' => 0,
                'material_cost' => 0,
                'total_investment' => 0,
                'profit_margin' => 0,
                'bid_value' => 0,
            ];
        }

        // Percentuais padrão do mercado
        $taxRate = 0.15; // 15% impostos
        $laborRate = 0.50; // 50% mão de obra
        $materialRate = 0.25; // 25% materiais
        $profitMargin = 10; // 10% margem

        $taxCost = $estimatedValue * $taxRate;
        $laborCost = $estimatedValue * $laborRate;
        $materialCost = $estimatedValue * $materialRate;

        $totalInvestment = $laborCost + $materialCost + $taxCost;
        $bidValue = $totalInvestment * (1 + $profitMargin / 100);

        return [
            'tax_cost' => round($taxCost, 2),
            'labor_cost' => round($laborCost, 2),
            'material_cost' => round($materialCost, 2),
            'total_investment' => round($totalInvestment, 2),
            'profit_margin' => $profitMargin,
            'bid_value' => round($bidValue, 2),
        ];
    }

    /**
     * Gera recomendação de participação
     */
    protected function generateRecommendation(array $compliance, array $costs, array $analysis): array
    {
        $score = 0;
        $reasons = [];
        $warnings = [];

        // Análise de documentação (40 pontos)
        $compliancePerc = $compliance['company_compliance']['percentage'] ?? 0;
        if ($compliancePerc >= 100) {
            $score += 40;
            $reasons[] = "✓ Empresa possui toda documentação necessária";
        } elseif ($compliancePerc >= 80) {
            $score += 30;
            $warnings[] = "⚠ Faltam alguns documentos ({$compliancePerc}% completo)";
        } elseif ($compliancePerc >= 50) {
            $score += 15;
            $warnings[] = "⚠ Documentação incompleta ({$compliancePerc}% completo)";
        } else {
            $warnings[] = "✗ Muitos documentos faltando ({$compliancePerc}% completo)";
        }

        // Análise de margem (30 pontos)
        $profitMargin = $costs['profit_margin'] ?? 0;
        if ($profitMargin >= 15) {
            $score += 30;
            $reasons[] = "✓ Margem de lucro excelente ({$profitMargin}%)";
        } elseif ($profitMargin >= 10) {
            $score += 25;
            $reasons[] = "✓ Margem de lucro adequada ({$profitMargin}%)";
        } elseif ($profitMargin >= 5) {
            $score += 15;
            $warnings[] = "⚠ Margem de lucro baixa ({$profitMargin}%)";
        } else {
            $warnings[] = "✗ Margem de lucro insuficiente ({$profitMargin}%)";
        }

        // Análise de valor (20 pontos)
        $estimatedValue = $analysis['estimated_value'] ?? 0;
        if ($estimatedValue >= 500000) {
            $score += 20;
            $reasons[] = "✓ Valor do contrato muito significativo (R$ " . number_format($estimatedValue, 2, ',', '.') . ")";
        } elseif ($estimatedValue >= 100000) {
            $score += 15;
            $reasons[] = "✓ Valor do contrato relevante (R$ " . number_format($estimatedValue, 2, ',', '.') . ")";
        } elseif ($estimatedValue >= 50000) {
            $score += 10;
            $reasons[] = "○ Valor moderado (R$ " . number_format($estimatedValue, 2, ',', '.') . ")";
        } else {
            $warnings[] = "⚠ Valor baixo para o investimento necessário";
        }

        // Análise de complexidade (10 pontos)
        $requirements = $analysis['requirements'] ?? [];
        if (count($requirements) <= 5) {
            $score += 10;
            $reasons[] = "✓ Requisitos gerenciáveis (" . count($requirements) . " requisitos)";
        } elseif (count($requirements) <= 10) {
            $score += 5;
            $reasons[] = "○ Requisitos moderados (" . count($requirements) . " requisitos)";
        } else {
            $warnings[] = "⚠ Muitos requisitos (" . count($requirements) . " requisitos)";
        }

        $worthParticipating = $score >= 60;

        if ($worthParticipating) {
            $recommendation = "✅ ALTAMENTE RECOMENDADO participar desta licitação!\n\n" .
                            implode("\n", $reasons);
            if (!empty($warnings)) {
                $recommendation .= "\n\nPontos de atenção:\n" . implode("\n", $warnings);
            }
        } elseif ($score >= 40) {
            $recommendation = "⚠️ AVALIAR COM CUIDADO antes de participar.\n\n" .
                            implode("\n", array_merge($reasons, $warnings));
        } else {
            $recommendation = "❌ NÃO RECOMENDADO participar no momento.\n\n" .
                            implode("\n", $warnings);
            if (!empty($reasons)) {
                $recommendation .= "\n\nPontos positivos:\n" . implode("\n", $reasons);
            }
        }

        return [
            'worth_participating' => $worthParticipating,
            'ai_score' => min(100, $score),
            'ai_recommendation' => $recommendation,  // Nome esperado pelo frontend
            'participation_recommendation' => $recommendation,  // Mantém compatibilidade
        ];
    }
}
