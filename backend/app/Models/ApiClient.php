<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;

use Database\Factories\ApiClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'created_by',
    'name',
    'client_key',
    'secret',
    'permissions',
    'is_active',
    'notes',
])]
class ApiClient extends Model
{
    /** @use HasFactory<ApiClientFactory> */
    use BelongsToAgency, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
