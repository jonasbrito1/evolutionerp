<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Edict extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'edict_number',
        'uasg_number',
        'process_number',
        'organ',
        'category',
        'modality',
        'description',
        'object_description',
        'requirements_text',
        'estimated_value',
        'minimum_value',
        'labor_cost',
        'material_cost',
        'tax_cost',
        'total_investment',
        'profit_margin',
        'unit_value',
        'bid_value',
        'publication_date',
        'closing_date',
        'opening_date',
        'proposal_deadline',
        'session_date',
        'status',
        'source_url',
        'bidding_portal_url',
        'file_path',
        'extracted_data',
        'requirements',
        'company_compliance',
        'missing_requirements',
        'available_documents',
        'worth_participating',
        'participation_recommendation',
        'ai_score',
        'processing_status',
        'processing_error',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'closing_date' => 'date',
        'opening_date' => 'date',
        'proposal_deadline' => 'datetime',
        'session_date' => 'datetime',
        'processed_at' => 'datetime',
        'extracted_data' => 'json',
        'requirements' => 'json',
        'company_compliance' => 'json',
        'missing_requirements' => 'json',
        'available_documents' => 'json',
        'worth_participating' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tenders(): HasMany
    {
        return $this->hasMany(Tender::class);
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(EdictAnalysis::class);
    }
}
