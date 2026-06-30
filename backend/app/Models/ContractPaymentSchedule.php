<?php

namespace App\Models;

use Database\Factories\ContractPaymentScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'contract_id',
    'schedule_number',
    'payment_type',
    'due_date',
    'amount_due',
    'currency',
    'amount_paid',
    'remaining_amount',
    'status',
    'paid_at',
    'days_late',
    'penalty_amount',
    'penalty_is_manual',
    'owner_amount_due',
    'owner_amount_paid',
    'owner_disbursement_status',
    'receipt_number',
    'receipt_generated_at',
    'notes',
])]
class ContractPaymentSchedule extends Model
{
    /** @use HasFactory<ContractPaymentScheduleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'days_late' => 'integer',
            'penalty_amount' => 'decimal:2',
            'penalty_is_manual' => 'boolean',
            'owner_amount_due' => 'decimal:2',
            'owner_amount_paid' => 'decimal:2',
            'receipt_generated_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
