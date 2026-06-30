<?php

namespace App\Models;

use Database\Factories\OwnerFactory;
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
    'bank_name',
    'rib_iban',
    'notes',
    'status',
])]
class Owner extends Model
{
    /** @use HasFactory<OwnerFactory> */
    use HasFactory, SoftDeletes;

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
