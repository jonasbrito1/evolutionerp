<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CompanyDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'document_category_id',
        'document_type',
        'category',
        'document_name',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'issue_date',
        'expiry_date',
        'status',
        'notes',
        'description',
        'reference_number',
        'metadata',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'metadata' => 'json',
    ];

    protected $appends = ['is_expired', 'is_expiring_soon', 'days_until_expiry'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isFuture() &&
               $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return $this->expiry_date->diffInDays(now(), false);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    // Methods
    public function updateStatus(): void
    {
        if ($this->is_expired) {
            $this->update(['status' => 'expired']);
        } elseif ($this->is_expiring_soon) {
            $this->update(['status' => 'pending_renewal']);
        }
    }

    // Categorias de documentos
    public static function getDocumentCategories(): array
    {
        return [
            'Certidoes' => 'Certidões',
            'Habilitacao' => 'Habilitação',
            'Atestados' => 'Atestados',
            'Outros' => 'Outros',
        ];
    }

    // Tipos de documentos organizados por categoria
    public static function getDocumentTypes(): array
    {
        return [
            'Certidoes' => [
                'certidao_federal' => 'Certidão Negativa de Débitos Federais',
                'certidao_estadual' => 'Certidão Negativa de Débitos Estaduais',
                'certidao_municipal' => 'Certidão Negativa de Débitos Municipais',
                'certidao_fgts' => 'Certidão Negativa de Débitos do FGTS',
                'certidao_trabalhista' => 'Certidão Negativa de Débitos Trabalhistas',
                'certidao_inss' => 'Certidão Negativa de Débitos - INSS',
                'certidao_falencia' => 'Certidão Negativa de Falência e Concordata',
            ],
            'Habilitacao' => [
                'cnpj' => 'Cadastro Nacional de Pessoa Jurídica (CNPJ)',
                'contrato_social' => 'Contrato Social / Estatuto',
                'ata_eleicao' => 'Ata de Eleição de Diretoria',
                'procuracao' => 'Procuração',
                'rg_representante' => 'RG do Representante Legal',
                'cpf_representante' => 'CPF do Representante Legal',
                'comprovante_endereco' => 'Comprovante de Endereço',
            ],
            'Atestados' => [
                'atestado_capacidade_tecnica' => 'Atestado de Capacidade Técnica',
                'atestado_visita_tecnica' => 'Atestado de Visita Técnica',
                'atestado_responsabilidade_tecnica' => 'Atestado de Responsabilidade Técnica (ART/RRT)',
            ],
            'Outros' => [
                'balanco_patrimonial' => 'Balanço Patrimonial',
                'demonstracao_resultado' => 'Demonstração de Resultado do Exercício (DRE)',
                'alvara_funcionamento' => 'Alvará de Funcionamento',
                'licenca_ambiental' => 'Licença Ambiental',
                'registro_profissional' => 'Registro Profissional (CREA/CRM/CRC)',
                'outros' => 'Outros Documentos',
            ],
        ];
    }

    // Obter todos os tipos em formato plano
    public static function getAllDocumentTypesFlat(): array
    {
        $types = [];
        foreach (self::getDocumentTypes() as $category => $categoryTypes) {
            foreach ($categoryTypes as $key => $label) {
                $types[$key] = $label;
            }
        }
        return $types;
    }

    // Obter categoria de um tipo de documento
    public static function getCategoryForType(string $type): ?string
    {
        foreach (self::getDocumentTypes() as $category => $categoryTypes) {
            if (array_key_exists($type, $categoryTypes)) {
                return $category;
            }
        }
        return 'Outros';
    }
}
