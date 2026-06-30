<?php

namespace App\Models;

use Database\Factories\ReportExportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'report_id',
    'saved_report_id',
    'requested_by',
    'export_number',
    'export_format',
    'status',
    'file_path',
    'file_name',
    'file_size',
    'parameters',
    'started_at',
    'completed_at',
    'failed_at',
    'error_message',
])]
class ReportExport extends Model
{
    /** @use HasFactory<ReportExportFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function savedReport(): BelongsTo { return $this->belongsTo(SavedReport::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
}
