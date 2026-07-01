<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ContractFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'property_id',
    'previous_contract_id',
    'assigned_agent_id',
    'created_by',
    'contract_number',
    'contract_type',
    'status',
    'start_date',
    'end_date',
    'signed_at',
    'amount',
    'currency',
    'deposit_amount',
    'charges_amount',
    'agency_fee_type',
    'agency_fee_value',
    'agency_fee_amount',
    'agency_fee_is_manual',
    'owner_amount',
    'owner_amount_is_manual',
    'notes',
])]
class Contract extends Model
{
    /** @use HasFactory<ContractFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_at' => 'datetime',
            'amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'charges_amount' => 'decimal:2',
            'agency_fee_value' => 'decimal:2',
            'agency_fee_amount' => 'decimal:2',
            'agency_fee_is_manual' => 'boolean',
            'owner_amount' => 'decimal:2',
            'owner_amount_is_manual' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function previousContract(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_contract_id');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
