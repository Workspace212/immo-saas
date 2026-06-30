<?php

namespace App\Models;

use Database\Factories\CollaborationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'property_id',
    'client_id',
    'requesting_agent_id',
    'owner_agent_id',
    'created_by',
    'collaboration_number',
    'collaboration_type',
    'status',
    'request_message',
    'rejection_reason',
    'accepted_at',
    'rejected_at',
    'completed_at',
    'cancelled_at',
])]
class Collaboration extends Model
{
    /** @use HasFactory<CollaborationFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function requestingAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_agent_id');
    }

    public function ownerAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
