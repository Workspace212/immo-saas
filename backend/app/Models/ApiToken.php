<?php

namespace App\Models;

use Database\Factories\ApiTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'api_client_id',
    'user_id',
    'name',
    'token',
    'permissions',
    'expires_at',
    'last_accessed_at',
    'is_active',
])]
class ApiToken extends Model
{
    /** @use HasFactory<ApiTokenFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function apiClient(): BelongsTo { return $this->belongsTo(ApiClient::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
