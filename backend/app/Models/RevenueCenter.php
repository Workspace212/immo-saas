<?php

namespace App\Models;

use Database\Factories\RevenueCenterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'property_id',
    'agent_id',
    'name',
    'code',
    'center_type',
    'project_name',
    'revenue_total',
    'expense_total',
    'profit_total',
    'statistics',
    'is_active',
    'notes',
])]
class RevenueCenter extends Model
{
    /** @use HasFactory<RevenueCenterFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'revenue_total' => 'decimal:2',
            'expense_total' => 'decimal:2',
            'profit_total' => 'decimal:2',
            'statistics' => 'array',
            'is_active' => 'boolean',
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

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
