<?php

namespace App\Models;

use Database\Factories\CollaborationCommissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'collaboration_id',
    'agent_id',
    'commission_role',
    'commission_type',
    'commission_value',
    'calculated_amount',
    'currency',
    'status',
    'validated_by',
    'validated_at',
    'notes',
])]
class CollaborationCommission extends Model
{
    /** @use HasFactory<CollaborationCommissionFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:2',
            'calculated_amount' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function collaboration(): BelongsTo
    {
        return $this->belongsTo(Collaboration::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
