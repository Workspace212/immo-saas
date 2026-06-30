<?php

namespace App\Models;

use Database\Factories\BankReconciliationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'financial_account_id',
    'validated_by',
    'reconciliation_number',
    'period_start',
    'period_end',
    'bank_balance',
    'system_balance',
    'difference_amount',
    'status',
    'validated_at',
    'notes',
])]
class BankReconciliation extends Model
{
    /** @use HasFactory<BankReconciliationFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'bank_balance' => 'decimal:2',
            'system_balance' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function validator(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
}
