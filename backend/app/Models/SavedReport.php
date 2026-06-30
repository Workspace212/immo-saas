<?php

namespace App\Models;

use Database\Factories\SavedReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'report_id',
    'user_id',
    'name',
    'slug',
    'configuration',
    'is_shared',
    'is_default',
    'notes',
])]
class SavedReport extends Model
{
    /** @use HasFactory<SavedReportFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'is_shared' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
