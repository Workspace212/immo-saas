<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\CreditNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'invoice_id',
    'created_by',
    'credit_note_number',
    'amount',
    'currency',
    'reason',
    'status',
    'issued_at',
    'pdf_path',
    'notes',
])]
class CreditNote extends Model
{
    /** @use HasFactory<CreditNoteFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'date',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
