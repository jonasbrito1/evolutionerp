# 🤖 Sistema Inteligente de Análise de Editais - Evolution CRM

## 📋 Visão Geral

Sistema completo de análise inteligente de editais de licitações públicas brasileiras com IA, incluindo:

- ✅ Upload e processamento automático de PDFs de editais
- ✅ Extração inteligente de informações (UASG, processo, valores, requisitos, datas)
- ✅ Análise de viabilidade baseada em documentação da empresa
- ✅ Cálculo automático de custos (impostos, mão de obra, materiais)
- ✅ Recomendação de participação
- ✅ Gestão completa de documentos da empresa
- ✅ Validação de requisitos contra documentação disponível

---

## 🗄️ Estrutura do Banco de Dados

### ✅ IMPLEMENTADO

#### Tabela: `edicts` (EXPANDIDA)
Novos campos adicionados:
```sql
- uasg_number (VARCHAR) - Número UASG
- process_number (VARCHAR) - Número do processo
- modality (VARCHAR) - Modalidade (Pregão, Dispensa, etc)
- bidding_portal_url (VARCHAR) - Link para portal
- labor_cost (DECIMAL) - Custo mão de obra
- material_cost (DECIMAL) - Custo materiais
- tax_cost (DECIMAL) - Custo impostos
- total_investment (DECIMAL) - Investimento total
- profit_margin (DECIMAL) - Margem de lucro %
- unit_value (DECIMAL) - Valor unitário
- bid_value (DECIMAL) - Valor de lance
- proposal_deadline (DATETIME) - Prazo proposta
- session_date (DATETIME) - Data sessão pública
- object_description (TEXT) - Objeto detalhado
- requirements (JSON) - Requisitos para participar
- company_compliance (JSON) - Empresa atende?
- missing_requirements (JSON) - Requisitos faltantes
- available_documents (JSON) - Documentos disponíveis
- worth_participating (BOOLEAN) - Vale a pena?
- participation_recommendation (TEXT) - Recomendação
- ai_score (INTEGER) - Score IA (0-100)
- processing_status (ENUM) - Status processamento
- processing_error (TEXT) - Erro processamento
- processed_at (TIMESTAMP) - Data processamento
```

#### Tabela: `company_documents` (NOVA)
```sql
- id (BIGINT PRIMARY KEY)
- company_id (FOREIGN KEY)
- document_type (VARCHAR) - Tipo documento
- document_name (VARCHAR) - Nome do documento
- file_path (VARCHAR) - Caminho do arquivo
- file_size (VARCHAR) - Tamanho
- mime_type (VARCHAR) - Tipo MIME
- issue_date (DATE) - Data emissão
- expiry_date (DATE) - Data validade
- status (ENUM: valid, expired, pending_renewal, invalid)
- notes (TEXT) - Observações
- metadata (JSON) - Dados extraídos
- uploaded_by (FOREIGN KEY users)
- created_at, updated_at, deleted_at
```

### Estrutura de Pastas
```
backend/storage/app/
├── docs/
│   └── empresa/           # Documentos da empresa
│       ├── cnpj/
│       ├── contratos/
│       ├── certidoes/
│       ├── balancos/
│       └── atestados/
└── editais/
    ├── uploads/           # PDFs enviados
    └── processed/         # PDFs processados
```

---

## 🔧 Componentes a Implementar

### 1. ⚙️ Service: EdictAnalysisService

**Arquivo:** `backend/app/Services/EdictAnalysisService.php`

**Responsabilidades:**
- Extrair texto de PDF usando biblioteca PHP (smalot/pdfparser ou similar)
- Processar texto com IA (OpenAI GPT-4 ou Claude API)
- Identificar campos-chave:
  - Número UASG/Pregão
  - Número do processo
  - Órgão
  - Objeto da licitação
  - Valor estimado
  - Datas (abertura, entrega proposta, sessão)
  - Requisitos de habilitação
  - Documentação exigida
  - Valores unitários/totais

**Prompt IA Exemplo:**
```
Analise o seguinte edital de licitação pública brasileira e extraia:

1. Número UASG/Pregão
2. Número do Processo
3. Órgão/Entidade
4. Modalidade (Pregão Eletrônico, Dispensa, etc)
5. Objeto da licitação (descrição completa)
6. Valor total estimado
7. Data de abertura
8. Data limite para envio de propostas
9. Data da sessão pública
10. Requisitos de habilitação
11. Documentação exigida
12. Link para portal de compras
13. Itens do edital (se houver) com quantidades e valores unitários

Retorne em JSON estruturado.

TEXTO DO EDITAL:
{texto_extraido}
```

**Método Principal:**
```php
public function analyzeEdict(Edict $edict, UploadedFile $pdf): array
{
    // 1. Extrair texto do PDF
    $extractedText = $this->extractTextFromPDF($pdf);

    // 2. Enviar para IA para extração estruturada
    $structuredData = $this->processWithAI($extractedText);

    // 3. Buscar documentos da empresa
    $companyDocs = $this->getCompanyDocuments($edict->company_id);

    // 4. Comparar requisitos com documentos disponíveis
    $compliance = $this->checkCompliance($structuredData['requirements'], $companyDocs);

    // 5. Calcular custos
    $costs = $this->calculateCosts($structuredData);

    // 6. Gerar recomendação
    $recommendation = $this->generateRecommendation($compliance, $costs);

    // 7. Retornar análise completa
    return [
        'extracted_data' => $structuredData,
        'compliance' => $compliance,
        'costs' => $costs,
        'recommendation' => $recommendation,
        'ai_score' => $this->calculateScore($compliance, $costs)
    ];
}
```

---

### 2. 🎮 Controller: EdictController

**Arquivo:** `backend/app/Http/Controllers/EdictController.php`

**Endpoints:**

```php
POST /api/edicts/upload
- Recebe PDF do edital
- Salva arquivo
- Dispara análise assíncrona (Job)
- Retorna ID do edital criado

GET /api/edicts
- Lista todos os editais
- Filtros: status, categoria, data, worth_participating
- Paginação

GET /api/edicts/{id}
- Detalhes completos do edital
- Inclui análise completa
- Inclui documentos relacionados

GET /api/edicts/{id}/analysis
- Retorna apenas análise inteligente
- Status de processamento
- Recomendações

PUT /api/edicts/{id}
- Atualiza dados manualmente
- Permite correções

POST /api/edicts/{id}/reanalyze
- Reprocessa análise
- Útil após atualizar documentos da empresa

DELETE /api/edicts/{id}
- Remove edital (soft delete)
```

---

### 3. 🎮 Controller: CompanyDocumentController

**Arquivo:** `backend/app/Http/Controllers/CompanyDocumentController.php`

**Endpoints:**

```php
GET /api/company/documents
- Lista todos os documentos da empresa
- Filtros: tipo, status, validade

GET /api/company/documents/{id}
- Detalhes do documento
- Permite visualização/download

POST /api/company/documents/upload
- Upload de novo documento
- Extração automática de metadados (datas, números)

PUT /api/company/documents/{id}
- Atualiza informações
- Atualiza arquivo

DELETE /api/company/documents/{id}
- Remove documento (soft delete)

GET /api/company/documents/expiring
- Lista documentos próximos do vencimento
- Alertas

GET /api/company/documents/types
- Lista tipos de documentos cadastrados
- Para filtros no frontend
```

---

### 4. ⚡ Job: ProcessEdictAnalysisJob

**Arquivo:** `backend/app/Jobs/ProcessEdictAnalysisJob.php`

**Responsabilidade:**
- Processa análise de forma assíncrona
- Evita timeout em uploads grandes
- Permite processamento em background

```php
php
public function handle(EdictAnalysisService $service)
{
    $edict = Edict::find($this->edictId);

    try {
        $edict->update(['processing_status' => 'processing']);

        $analysis = $service->analyzeEdict($edict, $this->pdfPath);

        $edict->update([
            'processing_status' => 'completed',
            'processed_at' => now(),
            ...$analysis
        ]);

    } catch (\Exception $e) {
        $edict->update([
            'processing_status' => 'failed',
            'processing_error' => $e->getMessage()
        ]);
    }
}
```

---

### 5. 📱 Frontend - Views Vue.js

#### `frontend/src/views/Edicts/EdictUpload.vue`
- Área de upload (drag & drop)
- Preview do PDF
- Barra de progresso do processamento
- Feedback em tempo real

#### `frontend/src/views/Edicts/EdictsList.vue`
- Listagem com filtros
- Cards com informações principais
- Badges de status
- Score visual (0-100)
- Ação rápida "Participar"

#### `frontend/src/views/Edicts/EdictDetail.vue`
- Informações completas
- Abas:
  - Visão Geral
  - Análise Inteligente
  - Requisitos
  - Documentação
  - Custos
  - Cronograma
- Link direto para portal
- Botão "Baixar Edital"
- Botão "Reprocessar"

#### `frontend/src/views/Documents/DocumentsManager.vue`
- Lista de documentos da empresa
- Upload múltiplo
- Visualizador inline (PDF)
- Filtros por tipo/status
- Alertas de vencimento
- Ações: editar, excluir, baixar

---

## 🔌 Integrações Necessárias

### 1. API de IA

**Opção A: OpenAI**
```bash
composer require openai-php/client
```

**Opção B: Anthropic Claude**
```bash
composer require anthropic-php/client
```

**.env**
```
OPENAI_API_KEY=sk-xxxxxxxxxxxxx
ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxxxxx
AI_PROVIDER=openai  # ou anthropic
```

### 2. Extração de PDF

```bash
composer require smalot/pdfparser
```

ou

```bash
composer require spatie/pdf-to-text
```

### 3. Filas (Queue)

Configurar Redis ou Database para filas:

```bash
php artisan queue:table
php artisan migrate
```

**.env**
```
QUEUE_CONNECTION=database
```

**Iniciar worker:**
```bash
php artisan queue:work
```

---

## 📝 Exemplo de Implementação Completa

### EdictController.php (Simplificado)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Edict;
use App\Jobs\ProcessEdictAnalysisJob;
use Illuminate\Http\Request;

class EdictController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:51200', // 50MB
            'company_id' => 'required|exists:companies,id'
        ]);

        // Salvar arquivo
        $path = $request->file('pdf')->store('editais/uploads');

        // Criar registro
        $edict = Edict::create([
            'company_id' => $request->company_id,
            'file_path' => $path,
            'status' => 'draft',
            'processing_status' => 'pending',
        ]);

        // Disparar análise assíncrona
        ProcessEdictAnalysisJob::dispatch($edict->id, $path);

        return response()->json([
            'message' => 'Edital enviado para análise',
            'edict_id' => $edict->id,
            'status' => 'processing'
        ], 201);
    }

    public function index(Request $request)
    {
        $edicts = Edict::with(['company', 'analysis'])
            ->when($request->worth_participating, function($q, $value) {
                $q->where('worth_participating', $value);
            })
            ->when($request->status, function($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($edicts);
    }

    public function show($id)
    {
        $edict = Edict::with(['company', 'analysis', 'documents'])
            ->findOrFail($id);

        return response()->json($edict);
    }
}
```

---

## 🚀 Próximos Passos (ROADMAP)

### Fase 1: Backend Core (3-5 dias)
- [x] Migrations criadas
- [ ] Criar Models completos
- [ ] Criar EdictAnalysisService
- [ ] Criar EdictController
- [ ] Criar CompanyDocumentController
- [ ] Criar Job de processamento
- [ ] Configurar filas
- [ ] Integrar com API de IA
- [ ] Testes unitários

### Fase 2: Frontend (3-4 dias)
- [ ] Tela de upload de editais
- [ ] Lista de editais
- [ ] Detalhes do edital
- [ ] Gestão de documentos
- [ ] Visualizador de PDF
- [ ] Componentes de análise visual

### Fase 3: Refinamento (2-3 dias)
- [ ] Otimização de prompts IA
- [ ] Melhorar extração de dados
- [ ] Validações avançadas
- [ ] Sistema de notificações
- [ ] Dashboard de métricas
- [ ] Relatórios PDF

### Fase 4: Testes e Deploy (1-2 dias)
- [ ] Testes end-to-end
- [ ] Documentação de usuário
- [ ] Deploy em produção

---

## 💰 Custos Estimados

### API de IA (Mensal)
- **OpenAI GPT-4**: ~$20-50/mês (100-250 editais)
- **Anthropic Claude**: ~$15-40/mês (100-250 editais)

### Infraestrutura
- **Redis** (para filas): Gratuito (local) ou $5-10/mês (cloud)
- **Storage**: Considerar 5-10GB inicial

---

## 📚 Recursos Importantes

### Documentação Técnica
- [Laravel Queues](https://laravel.com/docs/queues)
- [OpenAI API](https://platform.openai.com/docs)
- [Anthropic Claude API](https://docs.anthropic.com)
- [PDF Parser](https://github.com/smalot/pdfparser)

### Portais de Licitação
- [ComprasNet](https://www.gov.br/compras/)
- [Portal Nacional de Contratações Públicas](https://www.gov.br/pncp/)
- [BLL - Bolsa de Licitações](https://bll.org.br/)

---

## ⚠️ Considerações Importantes

1. **Precisão da IA**: A extração nunca será 100% precisa. Sempre permitir edição manual.

2. **Custo de API**: Monitorar uso de tokens. Implementar cache para reprocessamentos.

3. **Performance**: PDFs grandes podem demorar. Sempre usar filas.

4. **Segurança**: Documentos são sensíveis. Implementar controle de acesso rigoroso.

5. **Backup**: Fazer backup regular dos PDFs e documentos.

6. **Logs**: Registrar todas as análises para auditoria e melhoria contínua.

---

## 🎯 Status Atual

✅ **Estrutura de Banco**: Completa
✅ **Migrations**: Executadas
✅ **Pastas**: Criadas
⏳ **Backend Services**: Pendente
⏳ **Controllers**: Pendente
⏳ **Frontend**: Pendente
⏳ **Integração IA**: Pendente

---

**Desenvolvido para Evolution CRM**
**Versão**: 1.0.0
**Data**: Outubro 2025
