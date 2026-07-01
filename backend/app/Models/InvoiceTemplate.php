<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\InvoiceTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'name',
    'slug',
    'logo_path',
    'primary_color',
    'secondary_color',
    'footer_text',
    'template_data',
    'is_default',
    'is_active',
])]
class InvoiceTemplate extends Model
{
    /** @use HasFactory<InvoiceTemplateFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
