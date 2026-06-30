<?php

namespace App\Models;

use Database\Factories\PropertyInspectionPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_inspection_room_id',
    'uploaded_by',
    'file_path',
    'original_name',
    'title',
    'description',
    'taken_at',
    'display_order',
    'is_cover',
    'latitude',
    'longitude',
])]
class PropertyInspectionPhoto extends Model
{
    /** @use HasFactory<PropertyInspectionPhotoFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'is_cover' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function propertyInspectionRoom(): BelongsTo
    {
        return $this->belongsTo(PropertyInspectionRoom::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
