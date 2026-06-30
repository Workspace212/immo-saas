<?php

namespace App\Models;

use Database\Factories\ComplaintDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'complaint_id',
    'uploaded_by',
    'document_type',
    'file_path',
    'original_name',
    'notes',
])]
class ComplaintDocument extends Model
{
    /** @use HasFactory<ComplaintDocumentFactory> */
    use HasFactory, SoftDeletes;

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
