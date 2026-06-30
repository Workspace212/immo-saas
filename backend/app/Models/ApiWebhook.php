<?php

namespace App\Models;

use Database\Factories\ApiWebhookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'api_client_id',
    'name',
    'url',
    'event',
    'http_method',
    'secret',
    'headers',
    'is_active',
])]
class ApiWebhook extends Model
{
    /** @use HasFactory<ApiWebhookFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function apiClient(): BelongsTo { return $this->belongsTo(ApiClient::class); }
}
