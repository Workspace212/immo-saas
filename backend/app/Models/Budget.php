<?php

namespace App\Models;

use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'financial_category_id',
    'revenue_center_id',
    'created_by',
    'budget_number',
    'name',
    'budget_year',
    'period_type',
    'period_number',
    'planned_amount',
    'consumed_amount',
    'remaining_amount',
    'currency',
    'alert_threshold_percent',
    'alerts_enabled',
    'status',
    'notes',
])]
class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
            'consumed_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'alert_threshold_percent' => 'decimal:2',
            'alerts_enabled' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function financialCategory(): BelongsTo { return $this->belongsTo(FinancialCategory::class); }
    public function revenueCenter(): BelongsTo { return $this->belongsTo(RevenueCenter::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
