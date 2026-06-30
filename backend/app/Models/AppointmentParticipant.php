<?php

namespace App\Models;

use Database\Factories\AppointmentParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'appointment_id',
    'user_id',
    'client_id',
    'owner_id',
    'provider_id',
    'participant_type',
    'name',
    'email',
    'phone',
    'status',
    'responded_at',
    'notes',
])]
class AppointmentParticipant extends Model
{
    /** @use HasFactory<AppointmentParticipantFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
