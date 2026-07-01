<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;

trait BelongsToAgency
{
    public static function bootBelongsToAgency(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model): void {
            if (! empty($model->getAttribute('agency_id'))) {
                return;
            }

            $agencyId = app(TenantContext::class)->agencyId();

            if ($agencyId !== null) {
                $model->setAttribute('agency_id', $agencyId);

                return;
            }

            if (! TenantContext::isBypassed()) {
                // TODO: Throw when all jobs, commands, and seeders set tenant context explicitly.
            }
        });
    }
}
