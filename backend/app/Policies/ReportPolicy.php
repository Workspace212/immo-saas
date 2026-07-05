<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'agent', 'employee'])
            && $this->hasAgency($user)
            && $user->can('reports.viewAny');
    }

    public function view(User $user, Report $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('reports.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return true;
        }

        if ($user->hasAnyRole(['agent', 'employee'])) {
            return $this->isCreator($user, $model) || $this->hasReportShare($user, $model, canEdit: false);
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'agent', 'employee'])
            && $this->hasAgency($user)
            && $user->can('reports.create');
    }

    public function update(User $user, Report $model): bool
    {
        return $this->canMutate($user, $model, 'reports.update');
    }

    public function delete(User $user, Report $model): bool
    {
        return $this->canMutate($user, $model, 'reports.delete');
    }

    public function archive(User $user, Report $model): bool
    {
        return $this->canMutate($user, $model, 'reports.archive');
    }

    public function restore(User $user, Report $model): bool
    {
        return $this->canMutate($user, $model, 'reports.restore');
    }

    public function generate(User $user, Report $model): bool
    {
        return $this->view($user, $model);
    }

    public function exportPdf(User $user, Report $model): bool
    {
        return $this->export($user, $model);
    }

    public function exportExcel(User $user, Report $model): bool
    {
        return $this->export($user, $model);
    }

    public function exportCsv(User $user, Report $model): bool
    {
        return $this->export($user, $model);
    }

    public function schedule(User $user, Report $model): bool
    {
        return $this->canMutate($user, $model, 'reports.manage');
    }

    public function share(User $user, Report $model): bool
    {
        return $this->canMutate($user, $model, 'reports.share');
    }

    public function forceDelete(User $user, Report $model): bool
    {
        return false;
    }

    private function export(User $user, Report $report): bool
    {
        return $this->view($user, $report) && $user->can('reports.export');
    }

    private function canMutate(User $user, Report $report, string $permission): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $report)) {
            return false;
        }

        if (! $user->can($permission)) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return true;
        }

        if ($user->hasAnyRole(['agent', 'employee'])) {
            return $this->isCreator($user, $report) || $this->hasReportShare($user, $report, canEdit: true);
        }

        return false;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    private function hasExternalRole(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'client', 'provider']);
    }

    private function hasAgency(User $user): bool
    {
        return $user->agency_id !== null;
    }

    private function sameAgency(User $user, Report $report): bool
    {
        return $user->agency_id !== null
            && $report->agency_id !== null
            && (int) $user->agency_id === (int) $report->agency_id;
    }

    private function isCreator(User $user, Report $report): bool
    {
        return $report->created_by !== null && (int) $report->created_by === (int) $user->getKey();
    }

    private function hasReportShare(User $user, Report $report, bool $canEdit): bool
    {
        $query = DB::table('report_shares')
            ->where('report_id', $report->getKey())
            ->where('shared_with_user_id', $user->getKey())
            ->where('can_view', true)
            ->whereNull('deleted_at')
            ->where(function ($builder): void {
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        if ($canEdit) {
            $query->where('can_edit', true);
        }

        return $query->exists();
    }
}
