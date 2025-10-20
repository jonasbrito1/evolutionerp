<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tender_id',
        'description',
        'amount',
        'billing_date',
        'payment_date',
        'status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'payment_date' => 'date',
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
