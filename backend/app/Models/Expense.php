<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tender_id',
        'description',
        'category',
        'amount',
        'expense_date',
        'status',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }
}
