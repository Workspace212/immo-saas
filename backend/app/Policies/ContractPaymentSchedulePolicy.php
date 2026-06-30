<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContractPaymentSchedule;

class ContractPaymentSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ContractPaymentSchedule records.
        return false;
    }

    public function view(User $user, ContractPaymentSchedule $model): bool
    {
        // TODO: Implement business rules for viewing this ContractPaymentSchedule record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ContractPaymentSchedule records.
        return false;
    }

    public function update(User $user, ContractPaymentSchedule $model): bool
    {
        // TODO: Implement business rules for updating this ContractPaymentSchedule record.
        return false;
    }

    public function delete(User $user, ContractPaymentSchedule $model): bool
    {
        // TODO: Implement business rules for deleting this ContractPaymentSchedule record.
        return false;
    }

    public function restore(User $user, ContractPaymentSchedule $model): bool
    {
        // TODO: Implement business rules for restoring this ContractPaymentSchedule record.
        return false;
    }

    public function forceDelete(User $user, ContractPaymentSchedule $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ContractPaymentSchedule record.
        return false;
    }
}
