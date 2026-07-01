<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ScheduledReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'report_id',
    'saved_report_id',
    'created_by',
    'name',
    'frequency',
    'send_time',
    'recipients',
    'email_enabled',
    'whatsapp_enabled',
    'last_sent_at',
    'next_run_at',
    'is_active',
    'notes',
])]
class ScheduledReport extends Model
{
    /** @use HasFactory<ScheduledReportFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'send_time' => 'datetime:H:i',
            'recipients' => 'array',
            'email_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'last_sent_at' => 'datetime',
            'next_run_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function savedReport(): BelongsTo { return $this->belongsTo(SavedReport::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
