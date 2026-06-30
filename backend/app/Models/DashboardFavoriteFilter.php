<?php

namespace App\Models;

use Database\Factories\DashboardFavoriteFilterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'user_id',
    'module',
    'name',
    'search_query',
    'filters',
    'is_favorite',
    'is_shared',
    'display_order',
])]
class DashboardFavoriteFilter extends Model
{
    /** @use HasFactory<DashboardFavoriteFilterFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'is_favorite' => 'boolean',
            'is_shared' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
