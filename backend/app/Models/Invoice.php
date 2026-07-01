<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'client_id',
    'owner_id',
    'contract_id',
    'financial_transaction_id',
    'created_by',
    'invoice_number',
    'status',
    'issued_at',
    'due_at',
    'currency',
    'subtotal_amount',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'paid_amount',
    'remaining_amount',
    'pdf_path',
    'notes',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'due_at' => 'date',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function owner(): BelongsTo { return $this->belongsTo(Owner::class); }
    public function contract(): BelongsTo { return $this->belongsTo(Contract::class); }
    public function financialTransaction(): BelongsTo { return $this->belongsTo(FinancialTransaction::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
