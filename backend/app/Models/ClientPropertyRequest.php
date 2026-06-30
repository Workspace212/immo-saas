<?php

namespace App\Models;

use Database\Factories\ClientPropertyRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id',
    'property_type_id',
    'city',
    'sector',
    'min_budget',
    'max_budget',
    'min_living_area_m2',
    'min_land_area_m2',
    'min_bedrooms',
    'min_bathrooms',
    'activity_type',
    'status',
    'notes',
    'created_by',
])]
class ClientPropertyRequest extends Model
{
    /** @use HasFactory<ClientPropertyRequestFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_budget' => 'decimal:2',
            'max_budget' => 'decimal:2',
            'min_living_area_m2' => 'decimal:2',
            'min_land_area_m2' => 'decimal:2',
            'min_bedrooms' => 'integer',
            'min_bathrooms' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
