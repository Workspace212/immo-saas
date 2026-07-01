<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ComplaintTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'name',
    'slug',
    'sla_hours',
    'default_priority',
    'is_active',
    'display_order',
])]
class ComplaintType extends Model
{
    /** @use HasFactory<ComplaintTypeFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
