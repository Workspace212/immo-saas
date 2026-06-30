<?php

namespace App\Models;

use Database\Factories\DashboardWidgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'user_id',
    'widget_key',
    'title',
    'widget_type',
    'position_x',
    'position_y',
    'width',
    'height',
    'visible_roles',
    'display_order',
    'is_visible',
    'settings',
])]
class DashboardWidget extends Model
{
    /** @use HasFactory<DashboardWidgetFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'visible_roles' => 'array',
            'settings' => 'array',
            'is_visible' => 'boolean',
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
