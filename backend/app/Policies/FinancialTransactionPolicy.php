<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FinancialTransaction;
use App\Models\User;

class FinancialTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && $user->can('accounting.viewAny');
    }

    public function view(User $user, FinancialTransaction $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->sameAgency($user, $model)
            && $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && $user->can('accounting.create');
    }

    public function update(User $user, FinancialTransaction $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->sameAgency($user, $model)
            && $this->isMutable($model)
            && $user->can('accounting.update');
    }

    public function delete(User $user, FinancialTransaction $model): bool
    {
        // TODO: permission catalog has no accounting.delete permission; keep deletion closed.
        return false;
    }

    public function validateTransaction(User $user, FinancialTransaction $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->sameAgency($user, $model)
            && $this->isValidatable($model)
            && $user->can('accounting.validate');
    }

    public function cancelTransaction(User $user, FinancialTransaction $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        // TODO: permission catalog has no accounting.cancel permission; require accounting.manage until split.
        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->sameAgency($user, $model)
            && ! in_array($model->status, ['cancelled', 'finalized', 'closed'], true)
            && $user->can('accounting.manage');
    }

    public function closePeriod(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && $user->can('accounting.manage');
    }

    public function dashboard(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || $user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && ($user->can('accounting.view') || $user->can('accounting.manage'));
    }

    public function restore(User $user, FinancialTransaction $model): bool
    {
        // TODO: permission catalog has no accounting.restore permission; keep restore closed.
        return false;
    }

    public function forceDelete(User $user, FinancialTransaction $model): bool
    {
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

    private function sameAgency(User $user, FinancialTransaction $transaction): bool
    {
        return $user->agency_id !== null
            && $transaction->agency_id !== null
            && (int) $user->agency_id === (int) $transaction->agency_id;
    }

    private function isMutable(FinancialTransaction $transaction): bool
    {
        return ! $transaction->is_reconciled
            && ! in_array($transaction->status, ['validated', 'cancelled', 'finalized', 'closed'], true);
    }

    private function isValidatable(FinancialTransaction $transaction): bool
    {
        return ! $transaction->is_reconciled
            && ! in_array($transaction->status, ['validated', 'cancelled', 'finalized', 'closed'], true);
    }
}
