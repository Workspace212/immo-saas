<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'created_by',
    'provider',
    'integration_type',
    'name',
    'settings',
    'credentials',
    'is_active',
    'last_synced_at',
])]
class Integration extends Model
{
    /** @use HasFactory<IntegrationFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'credentials' => 'array',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
