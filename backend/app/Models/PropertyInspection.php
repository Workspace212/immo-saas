<?php

namespace App\Models;

use Database\Factories\PropertyInspectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'rental_unit_id',
    'property_id',
    'created_by',
    'validated_by',
    'inspection_number',
    'inspection_type',
    'inspection_date',
    'status',
    'electricity_meter',
    'water_meter',
    'gas_meter',
    'keys_given',
    'remotes_given',
    'access_cards_given',
    'global_condition',
    'tenant_comments',
    'agency_comments',
    'validation_date',
    'notes',
])]
class PropertyInspection extends Model
{
    /** @use HasFactory<PropertyInspectionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inspection_date' => 'datetime',
            'validation_date' => 'datetime',
            'electricity_meter' => 'decimal:2',
            'water_meter' => 'decimal:2',
            'gas_meter' => 'decimal:2',
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

    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
