<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdictAnalysis extends Model
{
    use HasFactory;

    protected $table = 'edict_analysis';

    protected $fillable = [
        'edict_id',
        'company_id',
        'compatibility_score',
        'documentation_score',
        'margin_score',
        'success_rate_score',
        'viability_score',
        'recommendation',
        'strengths',
        'weaknesses',
        'missing_documents',
        'next_steps',
        'justification',
    ];

    protected $casts = [
        'strengths' => 'json',
        'weaknesses' => 'json',
        'missing_documents' => 'json',
        'next_steps' => 'json',
    ];

    // Relationships
    public function edict(): BelongsTo
    {
        return $this->belongsTo(Edict::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
