<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\DashboardLayoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'user_id',
    'name',
    'slug',
    'is_default',
    'is_shared',
    'layout',
    'settings',
])]
class DashboardLayout extends Model
{
    /** @use HasFactory<DashboardLayoutFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_shared' => 'boolean',
            'layout' => 'array',
            'settings' => 'array',
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
