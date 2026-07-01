<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ReportShareFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'report_id',
    'saved_report_id',
    'shared_with_user_id',
    'shared_by',
    'role',
    'can_view',
    'can_edit',
    'expires_at',
    'notes',
])]
class ReportShare extends Model
{
    /** @use HasFactory<ReportShareFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_edit' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function savedReport(): BelongsTo { return $this->belongsTo(SavedReport::class); }
    public function sharedWithUser(): BelongsTo { return $this->belongsTo(User::class, 'shared_with_user_id'); }
    public function sharer(): BelongsTo { return $this->belongsTo(User::class, 'shared_by'); }
}
