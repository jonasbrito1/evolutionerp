<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'category',
        'description',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'payment_method',
        'document_number',
        'related_edict_id',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'metadata' => 'json',
    ];

    protected $appends = ['is_overdue', 'days_overdue'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function edict(): BelongsTo
    {
        return $this->belongsTo(Edict::class, 'related_edict_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'pago' || $this->status === 'recebido' || $this->status === 'cancelado') {
            return false;
        }

        return $this->due_date < Carbon::now()->startOfDay();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return Carbon::now()->startOfDay()->diffInDays($this->due_date);
    }

    // Métodos auxiliares
    public static function getPaymentMethods(): array
    {
        return [
            'dinheiro' => 'Dinheiro',
            'transferencia' => 'Transferência Bancária',
            'boleto' => 'Boleto',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'pix' => 'PIX',
            'cheque' => 'Cheque',
            'outros' => 'Outros',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'pendente' => 'Pendente',
            'pago' => 'Pago',
            'recebido' => 'Recebido',
            'atrasado' => 'Atrasado',
            'cancelado' => 'Cancelado',
        ];
    }
}
