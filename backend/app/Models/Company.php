<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cnpj',
        'industry',
        'founding_date',
        'address',
        'phone',
        'email',
        'logo',
        'description',
        'status',
    ];

    protected $casts = [
        'founding_date' => 'date',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function edicts(): HasMany
    {
        return $this->hasMany(Edict::class);
    }

    public function tenders(): HasMany
    {
        return $this->hasMany(Tender::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function edictAnalysis(): HasMany
    {
        return $this->hasMany(EdictAnalysis::class);
    }
}
