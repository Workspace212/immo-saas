<?php

namespace App\Models;

use Database\Factories\FinancialAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'financial_document_id',
    'uploaded_by',
    'attachment_type',
    'file_path',
    'original_name',
    'display_order',
    'notes',
])]
class FinancialAttachment extends Model
{
    /** @use HasFactory<FinancialAttachmentFactory> */
    use HasFactory, SoftDeletes;

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function financialDocument(): BelongsTo { return $this->belongsTo(FinancialDocument::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}
