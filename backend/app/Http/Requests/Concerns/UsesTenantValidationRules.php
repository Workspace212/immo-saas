<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait UsesTenantValidationRules
{
    protected function tenantExists(string $table, string $column = 'id'): Exists
    {
        $agencyId = $this->tenantAgencyId();

        return Rule::exists($table, $column)
            ->where(static function ($query) use ($agencyId): void {
                $agencyId === null
                    ? $query->whereRaw('1 = 0')
                    : $query->where('agency_id', $agencyId);
            });
    }

    protected function sameAgencyUserExists(string $column = 'id'): Exists
    {
        $agencyId = $this->tenantAgencyId();

        return Rule::exists('users', $column)
            ->where(static function ($query) use ($agencyId): void {
                $agencyId === null
                    ? $query->whereRaw('1 = 0')
                    : $query->where('agency_id', $agencyId);
            });
    }

    protected function globalOrTenantExists(string $table, string $column = 'id'): Exists
    {
        $agencyId = $this->tenantAgencyId();

        return Rule::exists($table, $column)
            ->where(static function ($query) use ($agencyId): void {
                if ($agencyId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where(static function ($nested) use ($agencyId): void {
                    $nested->whereNull('agency_id')
                        ->orWhere('agency_id', $agencyId);
                });
            });
    }

    protected function tenantAgencyId(): ?int
    {
        $agencyId = app(TenantContext::class)->agencyId();

        if ($agencyId !== null) {
            return (int) $agencyId;
        }

        $user = $this->user();

        return $user instanceof User && $user->agency_id !== null
            ? (int) $user->agency_id
            : null;
    }
}
