<?php

namespace App\Models;

use Database\Factories\CollaborationVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'collaboration_id',
    'property_id',
    'client_id',
    'scheduled_by',
    'visit_date',
    'status',
    'feedback',
    'result',
])]
class CollaborationVisit extends Model
{
    /** @use HasFactory<CollaborationVisitFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'visit_date' => 'datetime',
        ];
    }

    public function collaboration(): BelongsTo
    {
        return $this->belongsTo(Collaboration::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}
