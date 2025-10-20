<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kanban_card_id',
        'user_id',
        'comment',
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
}
