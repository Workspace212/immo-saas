<?php

namespace App\Models;

use Database\Factories\AppointmentReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'appointment_id',
    'user_id',
    'reminder_type',
    'remind_at',
    'status',
    'sent_at',
    'channel',
    'error_message',
])]
class AppointmentReminder extends Model
{
    /** @use HasFactory<AppointmentReminderFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
