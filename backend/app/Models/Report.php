<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'created_by',
    'report_number',
    'name',
    'report_type',
    'category',
    'parameters',
    'filters',
    'period_start',
    'period_end',
    'visibility',
    'is_favorite',
    'is_active',
    'notes',
])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'filters' => 'array',
            'period_start' => 'date',
            'period_end' => 'date',
            'is_favorite' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
