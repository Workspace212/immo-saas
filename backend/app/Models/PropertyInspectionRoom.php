<?php

namespace App\Models;

use Database\Factories\PropertyInspectionRoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_inspection_id',
    'room_name',
    'room_type',
    'floor',
    'condition',
    'cleanliness',
    'comments',
    'display_order',
])]
class PropertyInspectionRoom extends Model
{
    /** @use HasFactory<PropertyInspectionRoomFactory> */
    use HasFactory, SoftDeletes;

    public function propertyInspection(): BelongsTo
    {
        return $this->belongsTo(PropertyInspection::class);
    }
}
