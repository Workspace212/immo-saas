<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\RentalUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'property_id',
    'contract_id',
    'previous_rental_unit_id',
    'assigned_agent_id',
    'created_by',
    'rental_number',
    'status',
    'start_date',
    'end_date',
    'rent_amount',
    'currency',
    'deposit_amount',
    'deposit_status',
    'is_renewal',
    'notes',
])]
class RentalUnit extends Model
{
    /** @use HasFactory<RentalUnitFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'is_renewal' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function previousRentalUnit(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_rental_unit_id');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
