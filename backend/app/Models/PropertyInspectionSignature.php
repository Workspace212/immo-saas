<?php

namespace App\Models;

use Database\Factories\PropertyInspectionSignatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_inspection_id',
    'signer_type',
    'signer_name',
    'signer_role',
    'signature_path',
    'signed',
    'signed_at',
    'refused',
    'refusal_reason',
])]
class PropertyInspectionSignature extends Model
{
    /** @use HasFactory<PropertyInspectionSignatureFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'signed' => 'boolean',
            'refused' => 'boolean',
            'signed_at' => 'datetime',
        ];
    }

    public function propertyInspection(): BelongsTo
    {
        return $this->belongsTo(PropertyInspection::class);
    }
}
