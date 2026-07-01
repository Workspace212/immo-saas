<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\NotificationQueueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'user_id',
    'notification_template_id',
    'channel',
    'recipient',
    'subject',
    'body',
    'payload',
    'status',
    'scheduled_at',
    'sent_at',
    'failed_at',
    'error_message',
    'attempts',
])]
class NotificationQueue extends Model
{
    /** @use HasFactory<NotificationQueueFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected $table = 'notification_queue';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
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

    public function notificationTemplate(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class);
    }
}
