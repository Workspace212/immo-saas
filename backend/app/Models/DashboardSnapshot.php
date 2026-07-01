<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\DashboardSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'created_by',
    'snapshot_key',
    'period_type',
    'snapshot_date',
    'metric_values',
    'comparison_values',
    'metadata',
])]
class DashboardSnapshot extends Model
{
    /** @use HasFactory<DashboardSnapshotFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'metric_values' => 'array',
            'comparison_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
