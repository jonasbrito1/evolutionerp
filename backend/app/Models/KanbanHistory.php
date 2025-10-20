<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanHistory extends Model
{
    use HasFactory;

    protected $table = 'kanban_history';

    protected $fillable = [
        'kanban_card_id',
        'user_id',
        'from_column_id',
        'to_column_id',
        'note',
    ];

    // Relationships
    public function card(): BelongsTo
    {
        return $this->belongsTo(KanbanCard::class, 'kanban_card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'from_column_id');
    }

    public function toColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'to_column_id');
    }
}
