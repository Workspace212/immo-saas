<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DashboardWidget;
use App\Models\User;

class DashboardWidgetPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'agent', 'employee'])
            && $this->hasAgency($user)
            && $user->can('dashboard.view');
    }

    public function view(User $user, DashboardWidget $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('dashboard.view') || ! $model->is_visible) {
            return false;
        }

        if ($user->can('dashboard.manage')) {
            return true;
        }

        return $this->isOwner($user, $model) || $this->roleCanSeeWidget($user, $model);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        // TODO: permission catalog has no dashboard.create permission; dashboard.manage gates widget writes.
        return $user->hasAnyRole(['manager', 'assistant', 'agent', 'employee'])
            && $this->hasAgency($user)
            && $user->can('dashboard.manage');
    }

    public function update(User $user, DashboardWidget $model): bool
    {
        return $this->canManageWidget($user, $model);
    }

    public function delete(User $user, DashboardWidget $model): bool
    {
        return $this->canManageWidget($user, $model);
    }

    public function archive(User $user, DashboardWidget $model): bool
    {
        return $this->canManageWidget($user, $model);
    }

    public function restore(User $user, DashboardWidget $model): bool
    {
        return $this->canManageWidget($user, $model);
    }

    public function forceDelete(User $user, DashboardWidget $model): bool
    {
        return false;
    }

    private function canManageWidget(User $user, DashboardWidget $widget): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $widget)) {
            return false;
        }

        if (! $user->can('dashboard.manage')) {
            return false;
        }

        if ($user->hasRole('manager')) {
            return true;
        }

        return $user->hasAnyRole(['assistant', 'agent', 'employee']) && $this->isOwner($user, $widget);
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

    private function sameAgency(User $user, DashboardWidget $widget): bool
    {
        return $user->agency_id !== null
            && $widget->agency_id !== null
            && (int) $user->agency_id === (int) $widget->agency_id;
    }

    private function isOwner(User $user, DashboardWidget $widget): bool
    {
        return $widget->user_id !== null && (int) $widget->user_id === (int) $user->getKey();
    }

    private function roleCanSeeWidget(User $user, DashboardWidget $widget): bool
    {
        if ($widget->visible_roles === null || $widget->visible_roles === []) {
            return true;
        }

        foreach ($widget->visible_roles as $role) {
            if (is_string($role) && $user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
