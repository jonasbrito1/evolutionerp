<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_name',
        'cnpj',
        'contact_person',
        'email',
        'phone',
        'address',
        'specialty',
        'rating',
        'status',
        'total_transactions',
        'total_spent',
        'notes',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
