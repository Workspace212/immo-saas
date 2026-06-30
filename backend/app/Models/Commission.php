<?php

namespace App\Models;

use Database\Factories\CommissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'agent_id',
    'contract_id',
    'property_id',
    'created_by',
    'validated_by',
    'commission_number',
    'operation_type',
    'commission_type',
    'commission_value',
    'base_amount',
    'calculated_amount',
    'paid_amount',
    'remaining_amount',
    'currency',
    'is_manual',
    'status',
    'validated_at',
    'notes',
])]
class Commission extends Model
{
    /** @use HasFactory<CommissionFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'calculated_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'is_manual' => 'boolean',
            'validated_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function agent(): BelongsTo { return $this->belongsTo(User::class, 'agent_id'); }
    public function contract(): BelongsTo { return $this->belongsTo(Contract::class); }
    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function validator(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
}
