<?php

namespace App\Models;

use Database\Factories\CollaborationDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'collaboration_id',
    'uploaded_by',
    'document_type',
    'file_path',
    'original_name',
    'notes',
])]
class CollaborationDocument extends Model
{
    /** @use HasFactory<CollaborationDocumentFactory> */
    use HasFactory, SoftDeletes;

    public function collaboration(): BelongsTo
    {
        return $this->belongsTo(Collaboration::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
