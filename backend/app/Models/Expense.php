<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'financial_category_id',
    'financial_account_id',
    'provider_id',
    'property_id',
    'contract_id',
    'complaint_id',
    'created_by',
    'validated_by',
    'expense_number',
    'expense_date',
    'title',
    'description',
    'amount_ht',
    'tax_rate',
    'tax_amount',
    'amount_ttc',
    'currency',
    'payment_method',
    'status',
    'validated_at',
    'attachment_path',
    'notes',
])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount_ht' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'amount_ttc' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function financialCategory(): BelongsTo { return $this->belongsTo(FinancialCategory::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function contract(): BelongsTo { return $this->belongsTo(Contract::class); }
    public function complaint(): BelongsTo { return $this->belongsTo(Complaint::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function validator(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
}
