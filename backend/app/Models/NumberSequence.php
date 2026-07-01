<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\NumberSequenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'sequence_key',
    'name',
    'prefix',
    'suffix',
    'number_length',
    'next_number',
    'is_active',
])]
class NumberSequence extends Model
{
    /** @use HasFactory<NumberSequenceFactory> */
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
