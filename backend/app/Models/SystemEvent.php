<?php

namespace App\Models;

use Database\Factories\SystemEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'user_id',
    'resolved_by',
    'event_key',
    'level',
    'message',
    'details',
    'is_resolved',
    'resolved_at',
    'occurred_at',
])]
class SystemEvent extends Model
{
    /** @use HasFactory<SystemEventFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
            'occurred_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
}
