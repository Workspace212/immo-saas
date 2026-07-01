<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\FinancialTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'source_account_id',
    'destination_account_id',
    'financial_category_id',
    'revenue_center_id',
    'created_by',
    'validated_by',
    'transaction_number',
    'transaction_type',
    'transaction_date',
    'amount',
    'currency',
    'reference',
    'description',
    'attachment_path',
    'status',
    'validated_at',
    'is_reconciled',
    'reconciled_at',
    'notes',
])]
class FinancialTransaction extends Model
{
    /** @use HasFactory<FinancialTransactionFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'validated_at' => 'datetime',
            'is_reconciled' => 'boolean',
            'reconciled_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function sourceAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'source_account_id'); }
    public function destinationAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'destination_account_id'); }
    public function financialCategory(): BelongsTo { return $this->belongsTo(FinancialCategory::class); }
    public function revenueCenter(): BelongsTo { return $this->belongsTo(RevenueCenter::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function validator(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
}
