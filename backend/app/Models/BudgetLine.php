<?php

namespace App\Models;

use Database\Factories\BudgetLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'budget_id',
    'financial_category_id',
    'name',
    'period_type',
    'period_number',
    'planned_amount',
    'actual_amount',
    'variance_amount',
    'currency',
    'notes',
])]
class BudgetLine extends Model
{
    /** @use HasFactory<BudgetLineFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'variance_amount' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function budget(): BelongsTo { return $this->belongsTo(Budget::class); }
    public function financialCategory(): BelongsTo { return $this->belongsTo(FinancialCategory::class); }
}
