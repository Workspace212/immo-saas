<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Enums\NotificationStatus;
use App\Enums\ProviderStatus;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintDocument;
use App\Models\ComplaintProvider;
use App\Models\Property;
use App\Models\Provider;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'documents',
        'providers',
    ];

    public function createComplaint(array $data, ?User $user = null): Complaint
    {
        return DB::transaction(function () use ($data, $user): Complaint {
            $documents = $data['documents'] ?? null;
            $providers = $data['providers'] ?? null;

            $complaintData = $this->normalizeComplaintData($this->withoutRelationData($data));
            $complaintData['complaint_number'] ??= $this->generateComplaintNumber();
            $complaintData['priority'] ??= $this->calculatePriority($complaintData);
            $complaintData['status'] ??= ComplaintStatus::NEW->value;

            if ($user !== null) {
                $complaintData['created_by'] = $user->getKey();
            }

            // TODO: Validate tenant/property relationship and SLA category before creating the complaint.
            $complaint = Complaint::query()->create($complaintData);

            if (is_array($documents)) {
                foreach ($documents as $documentData) {
                    if (is_array($documentData)) {
                        $this->addDocument($complaint, $documentData, $user);
                    }
                }
            }

            if (is_array($providers)) {
                $this->assignProviders($complaint, $providers);
            }

            $this->createNotificationPlaceholder($complaint, $user);
            $this->createActivityLog($complaint, 'created', $user, [
                'status' => $complaint->status,
                'priority' => $complaint->priority,
            ]);

            return $complaint->refresh();
        });
    }

    public function updateComplaint(Complaint $complaint, array $data, ?User $user = null): Complaint
    {
        return DB::transaction(function () use ($complaint, $data, $user): Complaint {
            $complaintData = $this->normalizeComplaintData($this->withoutRelationData($data));

            if ($complaintData !== []) {
                $complaint->fill($complaintData);
                $complaint->save();
            }

            if (array_key_exists('documents', $data) && is_array($data['documents'])) {
                foreach ($data['documents'] as $documentData) {
                    if (is_array($documentData)) {
                        $this->addDocument($complaint, $documentData, $user);
                    }
                }
            }

            if (array_key_exists('providers', $data) && is_array($data['providers'])) {
                $this->assignProviders($complaint, $data['providers']);
            }

            $this->createActivityLog($complaint, 'updated', $user, [
                'changed_fields' => array_keys($complaintData),
            ]);

            return $complaint->refresh();
        });
    }

    public function assignToUser(Complaint $complaint, User $assignee): Complaint
    {
        return DB::transaction(function () use ($complaint, $assignee): Complaint {
            // TODO: Validate assignee role, agency membership, and workload before assignment.
            $complaint->assigned_to = $assignee->getKey();
            $complaint->status = ComplaintStatus::ASSIGNED->value;
            $complaint->save();

            $this->createNotificationPlaceholder($complaint, $assignee);
            $this->createActivityLog($complaint, 'assigned_to_user', $assignee, [
                'assigned_to' => $assignee->getKey(),
            ]);

            return $complaint->refresh();
        });
    }

    public function assignProviders(Complaint $complaint, array $providers): void
    {
        DB::transaction(function () use ($complaint, $providers): void {
            $keptIds = [];

            foreach ($providers as $providerData) {
                if (! is_array($providerData) || ! isset($providerData['provider_id'])) {
                    continue;
                }

                $providerId = (int) $providerData['provider_id'];
                $provider = Provider::query()->find($providerId);

                if (! $provider instanceof Provider || ! (bool) $provider->is_active) {
                    // TODO: Support ProviderStatus once providers expose a status column.
                    continue;
                }

                $assignment = ComplaintProvider::query()->updateOrCreate(
                    [
                        'complaint_id' => $complaint->getKey(),
                        'provider_id' => $providerId,
                    ],
                    [
                        'assigned_at' => $providerData['assigned_at'] ?? now(),
                        'intervention_date' => $providerData['intervention_date'] ?? null,
                        'status' => $this->enumValue($providerData['status'] ?? 'assigned'),
                        'amount' => $providerData['amount'] ?? null,
                        'notes' => $providerData['notes'] ?? null,
                    ],
                );

                $keptIds[] = $assignment->getKey();
            }

            $query = ComplaintProvider::query()->where('complaint_id', $complaint->getKey());

            if ($keptIds === []) {
                $query->delete();
            } else {
                $query->whereNotIn('id', $keptIds)->delete();
            }

            if ($keptIds !== [] && $complaint->status !== ComplaintStatus::CLOSED->value) {
                $complaint->status = ComplaintStatus::WAITING_PROVIDER->value;
                $complaint->save();
            }

            $this->createActivityLog($complaint, 'providers_assigned', null, [
                'provider_count' => count($keptIds),
                'provider_status_reference' => ProviderStatus::ACTIVE->value,
            ]);
        });
    }

    public function addDocument(Complaint $complaint, array $documentData, ?User $user = null): ComplaintDocument
    {
        return DB::transaction(function () use ($complaint, $documentData, $user): ComplaintDocument {
            $data = array_merge($documentData, [
                'complaint_id' => $complaint->getKey(),
            ]);

            if ($user !== null) {
                $data['uploaded_by'] = $user->getKey();
            }

            $document = ComplaintDocument::query()->create($data);

            $this->createActivityLog($complaint, 'document_added', $user, [
                'document_id' => $document->getKey(),
                'document_type' => $document->document_type,
            ]);

            return $document;
        });
    }

    public function markSeen(Complaint $complaint): Complaint
    {
        return $this->setStatus($complaint, ComplaintStatus::SEEN, 'seen');
    }

    public function markInProgress(Complaint $complaint): Complaint
    {
        return $this->setStatus($complaint, ComplaintStatus::IN_PROGRESS, 'in_progress');
    }

    public function markWaitingProvider(Complaint $complaint): Complaint
    {
        return $this->setStatus($complaint, ComplaintStatus::WAITING_PROVIDER, 'waiting_provider');
    }

    public function resolveComplaint(Complaint $complaint, ?User $user = null): Complaint
    {
        return DB::transaction(function () use ($complaint, $user): Complaint {
            // TODO: Validate provider intervention report and payment information before resolving.
            $complaint->status = ComplaintStatus::RESOLVED->value;
            $complaint->save();

            $this->createActivityLog($complaint, 'resolved', $user);

            return $complaint->refresh();
        });
    }

    public function closeComplaint(Complaint $complaint, ?User $user = null): Complaint
    {
        return DB::transaction(function () use ($complaint, $user): Complaint {
            // TODO: Require client/owner confirmation before closing if configured by agency.
            $complaint->status = ComplaintStatus::CLOSED->value;
            $complaint->save();

            $this->createActivityLog($complaint, 'closed', $user);

            return $complaint->refresh();
        });
    }

    public function reopenComplaint(Complaint $complaint): Complaint
    {
        return DB::transaction(function () use ($complaint): Complaint {
            // TODO: Restrict reopen rules based on closure date and user permissions.
            $complaint->status = ComplaintStatus::IN_PROGRESS->value;
            $complaint->save();

            $this->createActivityLog($complaint, 'reopened');

            return $complaint->refresh();
        });
    }

    public function calculatePriority(array $data): string
    {
        // TODO: Replace with SLA rules by agency, property type, complaint type, and contract tier.
        $priority = $data['priority'] ?? null;

        if ($priority instanceof BackedEnum) {
            return (string) $priority->value;
        }

        if (is_string($priority) && $priority !== '') {
            return $priority;
        }

        $text = strtolower((string) (($data['title'] ?? '') . ' ' . ($data['description'] ?? '') . ' ' . ($data['complaint_type'] ?? '')));

        if (str_contains($text, 'fire') || str_contains($text, 'flood') || str_contains($text, 'security')) {
            return ComplaintPriority::CRITICAL->value;
        }

        if (str_contains($text, 'urgent') || str_contains($text, 'water') || str_contains($text, 'electric')) {
            return ComplaintPriority::URGENT->value;
        }

        if (str_contains($text, 'minor') || str_contains($text, 'cleaning')) {
            return ComplaintPriority::LOW->value;
        }

        return ComplaintPriority::NORMAL->value;
    }

    public function generateComplaintNumber(): string
    {
        $year = now()->year;

        // TODO: Scope complaint numbering by agency and protect against concurrent writes.
        $count = Complaint::query()
            ->where('complaint_number', 'like', sprintf('COMP-%d-%%', $year))
            ->count();

        return sprintf('COMP-%d-%06d', $year, $count + 1);
    }

    public function createActivityLog(
        Complaint $complaint,
        string $action,
        ?User $user = null,
        array $metadata = [],
    ): void {
        ActivityLog::query()->create([
            'agency_id' => $complaint->agency_id,
            'user_id' => $user?->getKey(),
            'module' => 'complaints',
            'action' => $action,
            'description' => sprintf('Complaint %s: %s', $complaint->complaint_number, $action),
            'level' => 'info',
            'metadata' => array_merge([
                'complaint_id' => $complaint->getKey(),
                'complaint_number' => $complaint->complaint_number,
                'status' => $complaint->status,
            ], $metadata),
            'logged_at' => now(),
        ]);
    }

    private function setStatus(Complaint $complaint, ComplaintStatus $status, string $action): Complaint
    {
        return DB::transaction(function () use ($complaint, $status, $action): Complaint {
            // TODO: Validate allowed complaint status transitions before saving.
            $complaint->status = $status->value;
            $complaint->save();

            $this->createActivityLog($complaint, $action);

            return $complaint->refresh();
        });
    }

    private function createNotificationPlaceholder(Complaint $complaint, ?User $user = null): void
    {
        // TODO: Route notifications to agency managers, assignees, clients, and providers.
        AppNotification::query()->create([
            'agency_id' => $complaint->agency_id,
            'user_id' => $complaint->assigned_to,
            'created_by' => $user?->getKey(),
            'notification_number' => $this->generateNotificationNumber(),
            'type' => 'complaint',
            'title' => 'Nouvelle réclamation',
            'message' => sprintf('Réclamation %s: %s', $complaint->complaint_number, $complaint->title),
            'data' => [
                'complaint_id' => $complaint->getKey(),
                'priority' => $complaint->priority,
                'status' => $complaint->status,
            ],
            'priority' => $complaint->priority ?? ComplaintPriority::NORMAL->value,
            'status' => NotificationStatus::UNREAD->value,
            'related_type' => Complaint::class,
            'related_id' => $complaint->getKey(),
        ]);
    }

    private function generateNotificationNumber(): string
    {
        $year = now()->year;

        // TODO: Replace with NumberSequence when notification numbering is finalized.
        $count = AppNotification::query()
            ->where('notification_number', 'like', sprintf('NOTIF-%d-%%', $year))
            ->count();

        return sprintf('NOTIF-%d-%06d', $year, $count + 1);
    }

    private function normalizeComplaintData(array $data): array
    {
        foreach (['priority', 'status'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] instanceof BackedEnum) {
                $data[$key] = $this->enumValue($data[$key]);
            }
        }

        return $data;
    }

    private function withoutRelationData(array $data): array
    {
        foreach (self::RELATION_KEYS as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
