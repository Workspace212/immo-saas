<?php

namespace App\Models;

use Database\Factories\ContractPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'contract_payment_schedule_id',
    'received_by',
    'payment_number',
    'payment_date',
    'amount',
    'currency',
    'payment_method',
    'reference',
    'bank_name',
    'status',
    'receipt_number',
    'receipt_generated_at',
    'proof_file',
    'notes',
])]
class ContractPayment extends Model
{
    /** @use HasFactory<ContractPaymentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount' => 'decimal:2',
            'receipt_generated_at' => 'datetime',
        ];
    }

    public function paymentSchedule(): BelongsTo
    {
        return $this->belongsTo(ContractPaymentSchedule::class, 'contract_payment_schedule_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
