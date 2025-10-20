<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'kanban_column_id',
        'edict_id',
        'title',
        'description',
        'edict_number',
        'estimated_value',
        'deadline',
        'session_date',
        'has_budget',
        'has_suppliers',
        'has_certificates',
        'has_documents',
        'team_approved',
        'order',
        'assigned_to',
        'created_by',
        'priority',
        'notes',
    ];

    protected $casts = [
        'deadline' => 'date',
        'session_date' => 'date',
        'has_budget' => 'boolean',
        'has_suppliers' => 'boolean',
        'has_certificates' => 'boolean',
        'has_documents' => 'boolean',
        'team_approved' => 'boolean',
    ];

    protected $appends = ['completion_percentage'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }

    public function edict(): BelongsTo
    {
        return $this->belongsTo(Edict::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(KanbanComment::class)->latest();
    }

    public function history(): HasMany
    {
        return $this->hasMany(KanbanHistory::class)->latest();
    }

    // Calcular porcentagem de conclusão do checklist
    public function getCompletionPercentageAttribute(): int
    {
        $total = 5; // Total de itens do checklist
        $completed = 0;

        if ($this->has_budget) $completed++;
        if ($this->has_suppliers) $completed++;
        if ($this->has_certificates) $completed++;
        if ($this->has_documents) $completed++;
        if ($this->team_approved) $completed++;

        return (int) (($completed / $total) * 100);
    }

    // Verificar se está pronto para próxima etapa
    public function isReadyToMove(): bool
    {
        return $this->completion_percentage === 100;
    }
}
