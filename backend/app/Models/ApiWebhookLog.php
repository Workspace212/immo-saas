<?php

namespace App\Models;

use Database\Factories\ApiWebhookLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'api_webhook_id',
    'event',
    'payload',
    'response_body',
    'http_code',
    'duration_ms',
    'status',
    'attempted_at',
    'error_message',
])]
class ApiWebhookLog extends Model
{
    /** @use HasFactory<ApiWebhookLogFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempted_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function apiWebhook(): BelongsTo { return $this->belongsTo(ApiWebhook::class); }
}
