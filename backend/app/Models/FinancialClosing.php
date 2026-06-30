<?php

namespace App\Models;

use Database\Factories\FinancialClosingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'closed_by',
    'closing_number',
    'closing_type',
    'period_year',
    'period_month',
    'closed_at',
    'is_locked',
    'locked_at',
    'total_revenue',
    'total_expense',
    'net_result',
    'status',
    'notes',
])]
class FinancialClosing extends Model
{
    /** @use HasFactory<FinancialClosingFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'total_revenue' => 'decimal:2',
            'total_expense' => 'decimal:2',
            'net_result' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function closer(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
}
