<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('invoices.viewAny');
    }

    public function view(User $user, Invoice $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('invoices.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessInvoice($user, $model);
        }

        if ($user->hasAnyRole(['owner', 'client', 'provider'])) {
            // TODO: owner/client/provider records have no user_id; cannot safely match portal user to invoice subject.
            return false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            // TODO: create(User) has no dossier context; require controller-level resource authorization before allowing agents.
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $this->hasAgency($user)
            && $user->can('invoices.create');
    }

    public function update(User $user, Invoice $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('invoices.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent')
            && $this->isEditableForAgent($model)
            && $this->agentCanAccessInvoice($user, $model);
    }

    public function delete(User $user, Invoice $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent') || ! $this->isDeletable($model)) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('invoices.delete');
    }

    public function markSent(User $user, Invoice $model): bool
    {
        if (! $this->sameAgency($user, $model) || $model->status !== 'draft') {
            return false;
        }

        return $this->backOfficeWorkflow($user, $model, 'invoices.update');
    }

    public function markPaid(User $user, Invoice $model): bool
    {
        return $this->financialWorkflow($user, $model);
    }

    public function markPartiallyPaid(User $user, Invoice $model): bool
    {
        return $this->financialWorkflow($user, $model);
    }

    public function markOverdue(User $user, Invoice $model): bool
    {
        return $this->financialWorkflow($user, $model);
    }

    public function cancel(User $user, Invoice $model): bool
    {
        if (! $this->sameAgency($user, $model) || in_array($model->status, ['paid', 'cancelled'], true)) {
            return false;
        }

        return $this->backOfficeWorkflow($user, $model, 'invoices.validate');
    }

    public function duplicate(User $user, Invoice $model): bool
    {
        if (! $this->view($user, $model)) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('invoices.create');
    }

    public function generatePdf(User $user, Invoice $model): bool
    {
        return $this->view($user, $model)
            && ($user->can('invoices.export') || $user->can('invoices.view'));
    }

    public function archive(User $user, Invoice $model): bool
    {
        return $this->backOfficeWorkflow($user, $model, 'invoices.archive');
    }

    public function restore(User $user, Invoice $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('invoices.restore');
    }

    public function forceDelete(User $user, Invoice $model): bool
    {
        return false;
    }

    private function backOfficeWorkflow(User $user, Invoice $invoice, string $permission): bool
    {
        // TODO: split invoice workflow permissions beyond update/validate/archive/export.
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $invoice)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can($permission);
    }

    private function financialWorkflow(User $user, Invoice $invoice): bool
    {
        if (in_array($invoice->status, ['cancelled'], true)) {
            return false;
        }

        return $this->backOfficeWorkflow($user, $invoice, 'invoices.validate');
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

    private function sameAgency(User $user, Invoice $invoice): bool
    {
        return $user->agency_id !== null
            && $invoice->agency_id !== null
            && (int) $user->agency_id === (int) $invoice->agency_id;
    }

    private function isEditableForAgent(Invoice $invoice): bool
    {
        return ! in_array($invoice->status, ['issued', 'partially_paid', 'paid', 'overdue', 'cancelled'], true);
    }

    private function isDeletable(Invoice $invoice): bool
    {
        return $invoice->status === 'draft';
    }

    private function agentCanAccessInvoice(User $user, Invoice $invoice): bool
    {
        $userId = (int) $user->getKey();

        if ((int) $invoice->created_by === $userId) {
            return true;
        }

        if ($invoice->contract_id === null) {
            return false;
        }

        $contract = DB::table('contracts')
            ->select(['id', 'property_id', 'assigned_agent_id', 'created_by'])
            ->where('id', (int) $invoice->contract_id)
            ->first();

        if ($contract === null) {
            return false;
        }

        if ((int) $contract->assigned_agent_id === $userId || (int) $contract->created_by === $userId) {
            return true;
        }

        if ($contract->property_id === null) {
            return false;
        }

        $propertyId = (int) $contract->property_id;

        return DB::table('properties')
            ->where('id', $propertyId)
            ->where(function ($query) use ($userId): void {
                $query->where('created_by', $userId)
                    ->orWhere('updated_by', $userId);
            })
            ->exists()
            || DB::table('rental_units')
                ->where('contract_id', (int) $contract->id)
                ->where('assigned_agent_id', $userId)
                ->exists()
            || DB::table('complaints')
                ->where('property_id', $propertyId)
                ->where('assigned_to', $userId)
                ->exists()
            || DB::table('collaborations')
                ->where('property_id', $propertyId)
                ->where(function ($query) use ($userId): void {
                    $query->where('requesting_agent_id', $userId)
                        ->orWhere('owner_agent_id', $userId)
                        ->orWhere('created_by', $userId);
                })
                ->exists();
    }
}
