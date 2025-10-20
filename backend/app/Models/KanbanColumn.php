<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'color',
        'order',
        'is_final',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(KanbanCard::class)->orderBy('order');
    }
}
