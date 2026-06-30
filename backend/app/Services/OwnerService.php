<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerService
{
    public function create(array $data, ?User $user = null): Owner
    {
        return DB::transaction(function () use ($data, $user): Owner {
            $ownerData = $this->normalizeOwnerData($data);
            $ownerData['status'] ??= 'active';

            // TODO: Validate duplicate owners by email, phone, CIN/passport, and agency policy.
            unset($user);

            return Owner::query()->create($ownerData);
        });
    }

    public function update(Owner $owner, array $data, ?User $user = null): Owner
    {
        return DB::transaction(function () use ($owner, $data, $user): Owner {
            $ownerData = $this->normalizeOwnerData($data);

            // TODO: Add owner change audit logging when the audit workflow is finalized.
            unset($user);

            if ($ownerData !== []) {
                $owner->fill($ownerData);
                $owner->save();
            }

            return $owner->refresh();
        });
    }

    public function archive(Owner $owner): Owner
    {
        return DB::transaction(function () use ($owner): Owner {
            // TODO: Prevent archiving owners with active mandates, unpaid disbursements, or live rentals.
            $owner->fill(['status' => 'archived']);
            $owner->save();

            return $owner->refresh();
        });
    }

    public function restore(Owner $owner): Owner
    {
        return DB::transaction(function () use ($owner): Owner {
            // TODO: Restore related records when a formal owner archive workflow exists.
            if (method_exists($owner, 'trashed') && $owner->trashed()) {
                $owner->restore();
            }

            $owner->fill(['status' => 'active']);
            $owner->save();

            return $owner->refresh();
        });
    }

    public function delete(Owner $owner): void
    {
        DB::transaction(function () use ($owner): void {
            // TODO: Enforce deletion rules for owners linked to properties, mandates, and disbursements.
            $owner->delete();
        });
    }

    public function generateOwnerNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('OWNER-%s-', $year);

        // TODO: Persist owner numbers if the owners table receives an owner_number column.
        $count = Owner::query()
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('%s%06d', $prefix, $count + 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeOwnerData(array $data): array
    {
        if (isset($data['owner_type']) && ! isset($data['type'])) {
            $data['type'] = $data['owner_type'];
        }

        if (! isset($data['full_name'])) {
            $fullName = trim((string) (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')));

            if ($fullName !== '') {
                $data['full_name'] = $fullName;
            }
        }

        if (! isset($data['cin_passport'])) {
            $data['cin_passport'] = $data['cin'] ?? $data['passport'] ?? null;
        }

        return array_intersect_key($data, array_flip([
            'agency_id',
            'type',
            'full_name',
            'cin_passport',
            'phone',
            'whatsapp',
            'email',
            'address',
            'city',
            'country',
            'company_name',
            'ice',
            'rc',
            'if_number',
            'patente',
            'representative_name',
            'bank_name',
            'rib_iban',
            'notes',
            'status',
        ]));
    }
}
