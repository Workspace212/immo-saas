<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\InvoiceLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'invoice_id',
    'description',
    'quantity',
    'unit_price',
    'discount_amount',
    'tax_rate',
    'tax_amount',
    'line_total',
    'display_order',
])]
class InvoiceLine extends Model
{
    /** @use HasFactory<InvoiceLineFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
