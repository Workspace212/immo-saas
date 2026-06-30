<?php

namespace App\Models;

use Database\Factories\UserSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'user_id',
    'session_token',
    'ip_address',
    'browser',
    'device',
    'location',
    'last_activity_at',
    'is_active',
    'force_logged_out',
    'force_logged_out_at',
])]
class UserSession extends Model
{
    /** @use HasFactory<UserSessionFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'is_active' => 'boolean',
            'force_logged_out' => 'boolean',
            'force_logged_out_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
