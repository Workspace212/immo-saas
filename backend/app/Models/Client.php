<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'type',
    'full_name',
    'cin_passport',
    'phone',
    'whatsapp',
    'email',
    'address',
    'city',
    'country',
    'company_name',
    'ice',
    'rc',
    'if_number',
    'patente',
    'representative_name',
    'notes',
    'status',
])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
