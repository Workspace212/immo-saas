<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AppNotification;
use App\Models\User;

class AppNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        // TODO: controller index is not recipient-scoped, so ordinary/portal listing remains closed here.
        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && $user->can('notifications.viewAny');
    }

    public function view(User $user, AppNotification $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model) || ! $user->can('notifications.view')) {
            return false;
        }

        return $this->isRecipient($user, $model) || $this->canAdministerNotifications($user);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && ($user->can('notifications.create') || $user->can('notifications.manage'));
    }

    public function update(User $user, AppNotification $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        // Generic update edits notification content/recipient, not recipient read state.
        return $this->canAdministerNotifications($user)
            && ($user->can('notifications.update') || $user->can('notifications.manage'));
    }

    public function delete(User $user, AppNotification $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($this->isRecipient($user, $model)) {
            return $user->can('notifications.delete');
        }

        return $this->canAdministerNotifications($user) && $user->can('notifications.delete');
    }

    public function archive(User $user, AppNotification $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($this->isRecipient($user, $model)) {
            return $user->can('notifications.archive') || $user->can('notifications.update');
        }

        return $this->canAdministerNotifications($user) && $user->can('notifications.archive');
    }

    public function restore(User $user, AppNotification $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($this->isRecipient($user, $model)) {
            return $user->can('notifications.restore') || $user->can('notifications.update');
        }

        return $this->canAdministerNotifications($user) && $user->can('notifications.restore');
    }

    public function markAsRead(User $user, AppNotification $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        return $this->isRecipient($user, $model) && $user->can('notifications.view');
    }

    public function markAllAsRead(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        return $this->hasAgency($user) && $user->can('notifications.view');
    }

    public function sendNow(User $user, AppNotification $model): bool
    {
        return $this->canSend($user, $model);
    }

    public function queue(User $user, AppNotification $model): bool
    {
        return $this->canSend($user, $model);
    }

    public function forceDelete(User $user, AppNotification $model): bool
    {
        return false;
    }

    private function canSend(User $user, AppNotification $notification): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $this->sameAgency($user, $notification)
            && $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('notifications.manage');
    }

    private function canAdministerNotifications(User $user): bool
    {
        return ! $this->hasExternalRole($user)
            && ! $user->hasRole('agent')
            && $user->hasAnyRole(['manager', 'assistant', 'employee']);
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

    private function sameAgency(User $user, AppNotification $notification): bool
    {
        return $user->agency_id !== null
            && $notification->agency_id !== null
            && (int) $user->agency_id === (int) $notification->agency_id;
    }

    private function isRecipient(User $user, AppNotification $notification): bool
    {
        return $notification->user_id !== null && (int) $notification->user_id === (int) $user->getKey();
    }
}
