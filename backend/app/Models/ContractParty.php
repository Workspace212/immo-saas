<?php

namespace App\Models;

use Database\Factories\ContractPartyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'contract_id',
    'owner_id',
    'client_id',
    'party_type',
    'role',
    'ownership_percentage',
    'display_order',
    'signed',
    'signed_at',
    'is_active',
    'notes',
])]
class ContractParty extends Model
{
    /** @use HasFactory<ContractPartyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ownership_percentage' => 'decimal:2',
            'signed' => 'boolean',
            'signed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
