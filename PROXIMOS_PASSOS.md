# 🚀 Próximos Passos - Sistema Inteligente Evolution CRM

## ✅ O QUE FOI IMPLEMENTADO (FUNDAÇÃO COMPLETA)

### 1. Banco de Dados ✅
- ✅ Tabela `edicts` expandida com 25+ campos para análise inteligente
- ✅ Tabela `company_documents` criada para gestão documental
- ✅ Migrations executadas com sucesso
- ✅ Índices criados para performance

### 2. Models Laravel ✅
- ✅ **CompanyDocument.php** - Completo com:
  - Relationships (company, uploader)
  - Accessors (is_expired, is_expiring_soon, days_until_expiry)
  - Scopes (valid, expired, expiringSoon, ofType)
  - Métodos auxiliares
  - Lista de tipos de documentos

- ✅ **Edict.php** - Atualizado com:
  - Todos os 40+ campos novos
  - Casts apropriados (JSON, DateTime, Boolean)
  - Relationships existentes mantidos

### 3. Infraestrutura ✅
- ✅ Biblioteca PDF Parser instalada (`smalot/pdfparser`)
- ✅ Estrutura de pastas criada (`docs/empresa`, `editais/uploads`, `editais/processed`)
- ✅ Sistema rodando (Backend + Frontend + MySQL)

### 4. Documentação ✅
- ✅ **IMPLEMENTACAO_SISTEMA_INTELIGENTE.md** - Especificação técnica completa
- ✅ **PROXIMOS_PASSOS.md** - Este arquivo com roadmap detalhado

---

## 🔧 O QUE PRECISA SER DESENVOLVIDO

### Fase 1: Services (CRÍTICO - 2-3 dias)

#### 1.1 PdfExtractionService
**Arquivo:** `backend/app/Services/PdfExtractionService.php`

```php
<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PdfExtractionService
{
    public function extractText(string $pdfPath): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        return $pdf->getText();
    }

    public function extractMetadata(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        return $pdf->getDetails();
    }
}
```

#### 1.2 EdictAnalysisService (CORE DO SISTEMA)
**Arquivo:** `backend/app/Services/EdictAnalysisService.php`

**Opção A: Com API Anthropic Claude (RECOMENDADO)**

```php
<?php

namespace App\Services;

use App\Models\Edict;
use App\Models\CompanyDocument;
use Illuminate\Http\UploadedFile;

class EdictAnalysisService
{
    protected $pdfService;

    public function __construct(PdfExtractionService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function analyzeEdict(Edict $edict, string $pdfPath): array
    {
        // 1. Extrair texto do PDF
        $text = $this->pdfService->extractText($pdfPath);

        // 2. Enviar para Claude API para análise
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

        return [
            ...$analysis,
            'company_compliance' => $compliance,
            ...$costs,
            ...$recommendation,
        ];
    }

    protected function analyzeWithClaude(string $text): array
    {
        $apiKey = env('ANTHROPIC_API_KEY');

        $prompt = "Analise este edital de licitação pública brasileira e extraia em JSON:\n\n" .
                  "1. uasg_number (string)\n" .
                  "2. process_number (string)\n" .
                  "3. modality (string: Pregão, Dispensa, Concorrência, etc)\n" .
                  "4. organ (string)\n" .
                  "5. object_description (string detalhada)\n" .
                  "6. estimated_value (float)\n" .
                  "7. opening_date (YYYY-MM-DD)\n" .
                  "8. proposal_deadline (YYYY-MM-DD HH:MM:SS)\n" .
                  "9. session_date (YYYY-MM-DD HH:MM:SS)\n" .
                  "10. requirements (array de strings)\n" .
                  "11. required_documents (array de strings)\n" .
                  "12. bidding_portal_url (string)\n" .
                  "13. unit_value (float, se houver)\n\n" .
                  "TEXTO DO EDITAL:\n\n{$text}\n\n" .
                  "Retorne APENAS o JSON, sem texto adicional.";

        // Chamada para API Claude
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 4096,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        $result = $response->json();
        $content = $result['content'][0]['text'] ?? '{}';

        return json_decode($content, true);
    }

    protected function checkCompliance(array $requirements, $companyDocs): array
    {
        $docTypes = $companyDocs->pluck('document_type')->toArray();

        $requiredDocTypes = [
            'cnpj',
            'certidao_federal',
            'certidao_estadual',
            'certidao_municipal',
            'certidao_fgts',
            'certidao_trabalhista',
        ];

        $hasDocuments = [];
        $missingDocuments = [];

        foreach ($requiredDocTypes as $docType) {
            if (in_array($docType, $docTypes)) {
                $hasDocuments[] = $docType;
            } else {
                $missingDocuments[] = $docType;
            }
        }

        $compliancePercentage = count($hasDocuments) / count($requiredDocTypes) * 100;

        return [
            'has_documents' => $hasDocuments,
            'missing_documents' => $missingDocuments,
            'compliance_percentage' => round($compliancePercentage, 2),
            'is_compliant' => count($missingDocuments) === 0,
        ];
    }

    protected function calculateCosts(array $analysis): array
    {
        $estimatedValue = $analysis['estimated_value'] ?? 0;

        // Cálculos baseados em percentuais típicos
        $taxCost = $estimatedValue * 0.15; // 15% impostos
        $laborCost = $estimatedValue * 0.50; // 50% mão de obra
        $materialCost = $estimatedValue * 0.25; // 25% materiais
        $profitMargin = 10; // 10% margem

        $totalInvestment = $laborCost + $materialCost + $taxCost;

        return [
            'tax_cost' => round($taxCost, 2),
            'labor_cost' => round($laborCost, 2),
            'material_cost' => round($materialCost, 2),
            'total_investment' => round($totalInvestment, 2),
            'profit_margin' => $profitMargin,
            'bid_value' => round($totalInvestment * (1 + $profitMargin/100), 2),
        ];
    }

    protected function generateRecommendation(array $compliance, array $costs, array $analysis): array
    {
        $worthParticipating = false;
        $aiScore = 0;
        $reasons = [];

        // Análise de viabilidade
        if ($compliance['is_compliant']) {
            $aiScore += 40;
            $reasons[] = "Empresa possui toda documentação necessária";
        } else {
            $reasons[] = "Faltam {$compliance['missing_documents']|count} documentos";
        }

        // Análise de margem
        if ($costs['profit_margin'] >= 10) {
            $aiScore += 30;
            $reasons[] = "Margem de lucro adequada ({$costs['profit_margin']}%)";
        }

        // Análise de valor
        $estimatedValue = $analysis['estimated_value'] ?? 0;
        if ($estimatedValue >= 100000) {
            $aiScore += 20;
            $reasons[] = "Valor do contrato é significativo";
        }

        // Análise de complexidade
        $requirements = $analysis['requirements'] ?? [];
        if (count($requirements) <= 5) {
            $aiScore += 10;
            $reasons[] = "Requisitos são gerenciáveis";
        }

        $worthParticipating = $aiScore >= 60;

        $recommendation = $worthParticipating
            ? "RECOMENDADO: Vale a pena participar desta licitação. " . implode('. ', $reasons)
            : "NÃO RECOMENDADO: " . implode('. ', $reasons);

        return [
            'worth_participating' => $worthParticipating,
            'ai_score' => $aiScore,
            'participation_recommendation' => $recommendation,
        ];
    }
}
```

**Configurar .env:**
```env
ANTHROPIC_API_KEY=sk-ant-api03-xxxxxxxxxxxxxxxxxxxx
```

---

### Fase 2: Controllers (2 dias)

#### 2.1 EdictController
**Arquivo:** `backend/app/Http/Controllers/EdictController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Edict;
use App\Services\EdictAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EdictController extends Controller
{
    protected $analysisService;

    public function __construct(EdictAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * POST /api/edicts/upload
     * Upload de PDF do edital
     */
    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:51200', // 50MB
            'company_id' => 'required|exists:companies,id',
        ]);

        // Salvar PDF
        $path = $request->file('pdf')->store('editais/uploads');
        $fullPath = Storage::path($path);

        // Criar registro
        $edict = Edict::create([
            'company_id' => $request->company_id,
            'file_path' => $path,
            'status' => 'draft',
            'processing_status' => 'pending',
        ]);

        try {
            // Analisar imediatamente (em produção, usar Job assíncrono)
            $edict->update(['processing_status' => 'processing']);

            $analysis = $this->analysisService->analyzeEdict($edict, $fullPath);

            $edict->update([
                ...$analysis,
                'processing_status' => 'completed',
                'processed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $edict->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Edital processado com sucesso',
            'edict' => $edict->fresh(),
        ], 201);
    }

    /**
     * GET /api/edicts
     * Listar editais
     */
    public function index(Request $request)
    {
        $query = Edict::with(['company', 'analysis'])
            ->when($request->worth_participating, function($q, $value) {
                $q->where('worth_participating', $value);
            })
            ->when($request->status, function($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->processing_status, function($q, $status) {
                $q->where('processing_status', $status);
            })
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate(20));
    }

    /**
     * GET /api/edicts/{id}
     * Detalhes do edital
     */
    public function show($id)
    {
        $edict = Edict::with(['company', 'analysis'])
            ->findOrFail($id);

        return response()->json($edict);
    }

    /**
     * PUT /api/edicts/{id}
     * Atualizar edital
     */
    public function update(Request $request, $id)
    {
        $edict = Edict::findOrFail($id);
        $edict->update($request->all());

        return response()->json($edict);
    }

    /**
     * DELETE /api/edicts/{id}
     * Remover edital
     */
    public function destroy($id)
    {
        $edict = Edict::findOrFail($id);
        $edict->delete();

        return response()->json(['message' => 'Edital removido com sucesso']);
    }
}
```

#### 2.2 CompanyDocumentController
**Arquivo:** `backend/app/Http/Controllers/CompanyDocumentController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyDocumentController extends Controller
{
    /**
     * GET /api/company/documents
     */
    public function index(Request $request)
    {
        $query = CompanyDocument::with(['company', 'uploader'])
            ->where('company_id', $request->user()->company_id)
            ->when($request->type, function($q, $type) {
                $q->where('document_type', $type);
            })
            ->when($request->status, function($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate(20));
    }

    /**
     * POST /api/company/documents/upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:10240', // 10MB
            'document_type' => 'required|string',
            'document_name' => 'required|string',
            'expiry_date' => 'nullable|date',
            'issue_date' => 'nullable|date',
        ]);

        $file = $request->file('document');
        $path = $file->store('docs/empresa/' . $request->document_type);

        $document = CompanyDocument::create([
            'company_id' => $request->user()->company_id,
            'document_type' => $request->document_type,
            'document_name' => $request->document_name,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'expiry_date' => $request->expiry_date,
            'issue_date' => $request->issue_date,
            'uploaded_by' => $request->user()->id,
            'status' => 'valid',
        ]);

        return response()->json($document, 201);
    }

    /**
     * GET /api/company/documents/{id}
     */
    public function show($id)
    {
        $document = CompanyDocument::findOrFail($id);
        return response()->json($document);
    }

    /**
     * GET /api/company/documents/{id}/download
     */
    public function download($id)
    {
        $document = CompanyDocument::findOrFail($id);
        return Storage::download($document->file_path, $document->document_name);
    }

    /**
     * DELETE /api/company/documents/{id}
     */
    public function destroy($id)
    {
        $document = CompanyDocument::findOrFail($id);
        Storage::delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Documento removido']);
    }

    /**
     * GET /api/company/documents/expiring
     */
    public function expiring()
    {
        $documents = CompanyDocument::expiringSoon(30)->get();
        return response()->json($documents);
    }
}
```

---

### Fase 3: Rotas API (30 min)

**Arquivo:** `backend/routes/api.php`

```php
// Adicionar após as rotas existentes:

Route::middleware('auth:sanctum')->group(function () {

    // Editais
    Route::prefix('edicts')->group(function () {
        Route::post('/upload', [EdictController::class, 'upload']);
        Route::get('/', [EdictController::class, 'index']);
        Route::get('/{id}', [EdictController::class, 'show']);
        Route::put('/{id}', [EdictController::class, 'update']);
        Route::delete('/{id}', [EdictController::class, 'destroy']);
    });

    // Documentos da Empresa
    Route::prefix('company/documents')->group(function () {
        Route::get('/', [CompanyDocumentController::class, 'index']);
        Route::post('/upload', [CompanyDocumentController::class, 'upload']);
        Route::get('/expiring', [CompanyDocumentController::class, 'expiring']);
        Route::get('/{id}', [CompanyDocumentController::class, 'show']);
        Route::get('/{id}/download', [CompanyDocumentController::class, 'download']);
        Route::delete('/{id}', [CompanyDocumentController::class, 'destroy']);
    });
});
```

---

## 📝 COMANDOS PARA CRIAR OS ARQUIVOS

```bash
cd backend

# Criar Services
mkdir -p app/Services
touch app/Services/PdfExtractionService.php
touch app/Services/EdictAnalysisService.php

# Criar Controllers
php artisan make:controller EdictController
php artisan make:controller CompanyDocumentController
```

---

## 🧪 TESTAR O SISTEMA

### 1. Upload de Edital
```bash
curl -X POST http://localhost:8000/api/edicts/upload \
  -H "Authorization: Bearer SEU_TOKEN" \
  -F "pdf=@edital.pdf" \
  -F "company_id=1"
```

### 2. Listar Editais
```bash
curl http://localhost:8000/api/edicts \
  -H "Authorization: Bearer SEU_TOKEN"
```

### 3. Upload de Documento
```bash
curl -X POST http://localhost:8000/api/company/documents/upload \
  -H "Authorization: Bearer SEU_TOKEN" \
  -F "document=@cnpj.pdf" \
  -F "document_type=cnpj" \
  -F "document_name=CNPJ da Empresa"
```

---

## ⚡ OTIMIZAÇÕES FUTURAS

### 1. Processamento Assíncrono com Filas
```php
// Job: ProcessEdictAnalysisJob
php artisan make:job ProcessEdictAnalysisJob

// No EdictController::upload(), em vez de:
$analysis = $this->analysisService->analyzeEdict($edict, $fullPath);

// Usar:
ProcessEdictAnalysisJob::dispatch($edict->id, $fullPath);
```

### 2. Notificações em Tempo Real
```bash
composer require pusher/pusher-php-server
```

### 3. OCR para PDFs Escaneados
```bash
composer require thiagoalessio/tesseract_ocr
```

---

## 💰 CUSTOS ESTIMADOS

### API Anthropic Claude
- **Modelo:** claude-3-5-sonnet-20241022
- **Custo:** ~$3 por milhão de tokens de input
- **Por edital:** ~$0.15-0.30 (assumindo 50k-100k tokens)
- **100 editais/mês:** $15-30/mês

---

## 🎯 STATUS FINAL

✅ Fundação 100% Completa
✅ Banco de Dados Pronto
✅ Models Criados
✅ Documentação Técnica
⏳ Services (copiar código acima)
⏳ Controllers (copiar código acima)
⏳ Rotas (copiar código acima)
⏳ Frontend

**Tempo estimado para conclusão: 2-3 dias de desenvolvimento**

---

Desenvolvido para Evolution CRM
Outubro 2025
