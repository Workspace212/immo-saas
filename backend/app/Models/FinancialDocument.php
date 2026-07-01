<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\FinancialDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'financial_transaction_id',
    'expense_id',
    'contract_id',
    'created_by',
    'document_number',
    'document_type',
    'title',
    'document_date',
    'amount',
    'currency',
    'status',
    'notes',
])]
class FinancialDocument extends Model
{
    /** @use HasFactory<FinancialDocumentFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function financialTransaction(): BelongsTo { return $this->belongsTo(FinancialTransaction::class); }
    public function expense(): BelongsTo { return $this->belongsTo(Expense::class); }
    public function contract(): BelongsTo { return $this->belongsTo(Contract::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
