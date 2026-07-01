<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\SearchIndexFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'module',
    'searchable_type',
    'searchable_id',
    'title',
    'content',
    'index_data',
    'indexed_at',
    'is_active',
])]
class SearchIndex extends Model
{
    /** @use HasFactory<SearchIndexFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'index_data' => 'array',
            'indexed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
