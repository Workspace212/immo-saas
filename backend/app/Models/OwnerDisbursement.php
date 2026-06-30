<?php

namespace App\Models;

use Database\Factories\OwnerDisbursementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'owner_id',
    'contract_id',
    'contract_payment_schedule_id',
    'financial_account_id',
    'created_by',
    'disbursement_number',
    'amount_due',
    'amount_paid',
    'remaining_amount',
    'currency',
    'scheduled_date',
    'paid_at',
    'payment_method',
    'status',
    'receipt_number',
    'notes',
])]
class OwnerDisbursement extends Model
{
    /** @use HasFactory<OwnerDisbursementFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'scheduled_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function owner(): BelongsTo { return $this->belongsTo(Owner::class); }
    public function contract(): BelongsTo { return $this->belongsTo(Contract::class); }
    public function paymentSchedule(): BelongsTo { return $this->belongsTo(ContractPaymentSchedule::class, 'contract_payment_schedule_id'); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
