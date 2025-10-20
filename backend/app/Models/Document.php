<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tender_id',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'category',
        'version',
        'uploaded_by',
        'description',
        'tags',
        'is_public',
        'expires_at',
    ];

    protected $casts = [
        'tags' => 'json',
        'expires_at' => 'datetime',
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

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
