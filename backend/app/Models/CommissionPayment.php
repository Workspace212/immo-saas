<?php

namespace App\Models;

use Database\Factories\CommissionPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'commission_id',
    'financial_account_id',
    'paid_by',
    'payment_number',
    'payment_date',
    'amount',
    'currency',
    'payment_method',
    'reference',
    'receipt_number',
    'receipt_path',
    'status',
    'notes',
])]
class CommissionPayment extends Model
{
    /** @use HasFactory<CommissionPaymentFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function commission(): BelongsTo { return $this->belongsTo(Commission::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function payer(): BelongsTo { return $this->belongsTo(User::class, 'paid_by'); }
}
