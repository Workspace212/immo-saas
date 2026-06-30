<?php

namespace App\Models;

use Database\Factories\IntegrationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'integration_id',
    'action',
    'result',
    'message',
    'context',
    'logged_at',
])]
class IntegrationLog extends Model
{
    /** @use HasFactory<IntegrationLogFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function integration(): BelongsTo { return $this->belongsTo(Integration::class); }
}
