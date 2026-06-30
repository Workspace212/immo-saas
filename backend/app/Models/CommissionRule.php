<?php

namespace App\Models;

use Database\Factories\CommissionRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'agent_id',
    'contract_type_id',
    'name',
    'team_name',
    'percentage',
    'fixed_amount',
    'currency',
    'priority',
    'is_active',
    'conditions',
])]
class CommissionRule extends Model
{
    /** @use HasFactory<CommissionRuleFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'conditions' => 'array',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function agent(): BelongsTo { return $this->belongsTo(User::class, 'agent_id'); }
    public function contractType(): BelongsTo { return $this->belongsTo(ContractType::class); }
}
