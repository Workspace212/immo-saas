<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::isBypassed()) {
            return;
        }

        $agencyId = app(TenantContext::class)->agencyId();

        if ($agencyId === null) {
            if (app()->runningInConsole()) {
                // TODO: Commands and jobs that query tenant-owned models must
                // set TenantContext::setAgencyId() or explicitly bypass scope.
                return;
            }

            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('agency_id'), $agencyId);
    }
}
