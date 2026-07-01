<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\CashMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'cash_account_id',
    'bank_account_id',
    'created_by',
    'movement_number',
    'movement_type',
    'movement_date',
    'amount',
    'currency',
    'reference',
    'comment',
    'status',
])]
class CashMovement extends Model
{
    /** @use HasFactory<CashMovementFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function cashAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'cash_account_id'); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'bank_account_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
