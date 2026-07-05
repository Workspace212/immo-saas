<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Collaboration;
use App\Models\User;

class CollaborationPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('collaborations.viewAny');
    }

    public function view(User $user, Collaboration $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('collaborations.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->isParticipant($user, $model);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('collaborations.create');
    }

    public function update(User $user, Collaboration $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('collaborations.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->isParticipant($user, $model);
    }

    public function delete(User $user, Collaboration $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('collaborations.delete');
    }

    public function accept(User $user, Collaboration $model): bool
    {
        if (! $this->canWorkflow($user, $model, requireParticipant: false)) {
            return false;
        }

        if ($model->status !== 'pending') {
            // TODO: move collaboration status transitions into an explicit state machine.
            return false;
        }

        if ($user->hasRole('agent')) {
            return (int) $model->owner_agent_id === (int) $user->getKey()
                && (int) $model->requesting_agent_id !== (int) $user->getKey();
        }

        return true;
    }

    public function reject(User $user, Collaboration $model): bool
    {
        return $this->accept($user, $model);
    }

    public function cancel(User $user, Collaboration $model): bool
    {
        if (! $this->canWorkflow($user, $model)) {
            return false;
        }

        return ! in_array($model->status, ['completed', 'cancelled'], true);
    }

    public function complete(User $user, Collaboration $model): bool
    {
        if (! $this->canWorkflow($user, $model)) {
            return false;
        }

        return in_array($model->status, ['accepted', 'in_progress'], true);
    }

    public function addMessage(User $user, Collaboration $model): bool
    {
        return $this->canShare($user, $model);
    }

    public function addDocument(User $user, Collaboration $model): bool
    {
        return $this->canShare($user, $model);
    }

    public function scheduleVisit(User $user, Collaboration $model): bool
    {
        return $this->canWorkflow($user, $model);
    }

    public function submitOffer(User $user, Collaboration $model): bool
    {
        return $this->canWorkflow($user, $model);
    }

    public function archive(User $user, Collaboration $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('collaborations.archive');
    }

    public function restore(User $user, Collaboration $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('collaborations.restore');
    }

    public function forceDelete(User $user, Collaboration $model): bool
    {
        return false;
    }

    private function canWorkflow(User $user, Collaboration $collaboration, bool $requireParticipant = true): bool
    {
        // TODO: permission catalog has no granular collaboration workflow permissions; using collaborations.update.
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $collaboration)) {
            return false;
        }

        if (! $user->can('collaborations.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && (! $requireParticipant || $this->isParticipant($user, $collaboration));
    }

    private function canShare(User $user, Collaboration $collaboration): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $collaboration)) {
            return false;
        }

        if (! $user->can('collaborations.share')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->isParticipant($user, $collaboration);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    private function hasInternalRole(User $user): bool
    {
        return $user->hasAnyRole(['manager', 'assistant', 'agent', 'employee']);
    }

    private function hasExternalRole(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'client', 'provider']);
    }

    private function hasAgency(User $user): bool
    {
        return $user->agency_id !== null;
    }

    private function sameAgency(User $user, Collaboration $collaboration): bool
    {
        return $user->agency_id !== null
            && $collaboration->agency_id !== null
            && (int) $user->agency_id === (int) $collaboration->agency_id;
    }

    private function isParticipant(User $user, Collaboration $collaboration): bool
    {
        $userId = (int) $user->getKey();

        return (int) $collaboration->requesting_agent_id === $userId
            || (int) $collaboration->owner_agent_id === $userId
            || (int) $collaboration->created_by === $userId;
    }
}
