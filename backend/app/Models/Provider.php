<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'provider_type',
    'company_name',
    'contact_name',
    'phone',
    'whatsapp',
    'email',
    'address',
    'city',
    'ice',
    'rc',
    'if_number',
    'patente',
    'rating',
    'is_active',
    'notes',
])]
class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
