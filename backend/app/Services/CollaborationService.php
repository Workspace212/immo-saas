<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CollaborationStatus;
use App\Enums\CommissionType;
use App\Enums\NotificationStatus;
use App\Enums\OfferStatus;
use App\Enums\VisitStatus;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Client;
use App\Models\Collaboration;
use App\Models\CollaborationCommission;
use App\Models\CollaborationDocument;
use App\Models\CollaborationMessage;
use App\Models\CollaborationOffer;
use App\Models\CollaborationVisit;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CollaborationService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'commissions',
    ];

    public function createCollaboration(array $data, ?User $user = null): Collaboration
    {
        return DB::transaction(function () use ($data, $user): Collaboration {
            $commissions = $data['commissions'] ?? null;
            $requestMessage = $data['request_message'] ?? null;
            $collaborationData = $this->withoutRelationData($data);

            $collaborationData['collaboration_number'] ??= $this->generateCollaborationNumber();

            if ($user !== null) {
                $collaborationData['created_by'] = $user->getKey();
            }

            // TODO: Validate duplicate collaboration requests and cross-agency permissions.
            $collaboration = Collaboration::query()->create($collaborationData);

            if (is_string($requestMessage) && trim($requestMessage) !== '') {
                $this->addMessage($collaboration, $requestMessage, $user);
            }

            if (is_array($commissions)) {
                $this->syncCommissions($collaboration, $commissions);
            }

            return $collaboration->refresh();
        });
    }

    public function updateCollaboration(Collaboration $collaboration, array $data, ?User $user = null): Collaboration
    {
        return DB::transaction(function () use ($collaboration, $data): Collaboration {
            $collaborationData = $this->withoutRelationData($data);

            if ($collaborationData !== []) {
                // TODO: Enforce status-specific editable fields and collaborator permissions.
                $collaboration->fill($collaborationData);
                $collaboration->save();
            }

            if (array_key_exists('commissions', $data) && is_array($data['commissions'])) {
                $this->syncCommissions($collaboration, $data['commissions']);
            }

            // TODO: Persist updater metadata when the collaborations table supports it.

            return $collaboration->refresh();
        });
    }

    public function acceptCollaboration(Collaboration $collaboration, ?User $user = null): Collaboration
    {
        return DB::transaction(function () use ($collaboration, $user): Collaboration {
            // TODO: Notify both agents and enforce who can accept the collaboration.
            $collaboration->fill([
                'status' => CollaborationStatus::ACCEPTED->value,
                'accepted_at' => Carbon::now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);
            $collaboration->save();

            $this->createActivityLogPlaceholder($collaboration, 'accepted', $user);

            return $collaboration->refresh();
        });
    }

    public function rejectCollaboration(Collaboration $collaboration, string $reason, ?User $user = null): Collaboration
    {
        return DB::transaction(function () use ($collaboration, $reason, $user): Collaboration {
            // TODO: Enforce rejection permissions and reason requirements by collaboration type.
            $collaboration->fill([
                'status' => CollaborationStatus::REJECTED->value,
                'rejected_at' => Carbon::now(),
                'rejection_reason' => $reason,
            ]);
            $collaboration->save();

            $this->createActivityLogPlaceholder($collaboration, 'rejected', $user);

            return $collaboration->refresh();
        });
    }

    public function cancelCollaboration(Collaboration $collaboration, ?User $user = null): Collaboration
    {
        return DB::transaction(function () use ($collaboration, $user): Collaboration {
            // TODO: Define cancellation rules for accepted collaborations with visits or offers.
            $collaboration->fill([
                'status' => CollaborationStatus::CANCELLED->value,
                'cancelled_at' => Carbon::now(),
            ]);
            $collaboration->save();

            $this->createActivityLogPlaceholder($collaboration, 'cancelled', $user);

            return $collaboration->refresh();
        });
    }

    public function completeCollaboration(Collaboration $collaboration, ?User $user = null): Collaboration
    {
        return DB::transaction(function () use ($collaboration, $user): Collaboration {
            // TODO: Require accepted offers, validated commissions, or closing documents as needed.
            $collaboration->fill([
                'status' => CollaborationStatus::COMPLETED->value,
                'completed_at' => Carbon::now(),
            ]);
            $collaboration->save();

            $this->createActivityLogPlaceholder($collaboration, 'completed', $user);

            return $collaboration->refresh();
        });
    }

    public function addMessage(
        Collaboration $collaboration,
        string $message,
        ?User $sender = null,
        bool $isInternal = true
    ): CollaborationMessage {
        return DB::transaction(function () use ($collaboration, $message, $sender, $isInternal): CollaborationMessage {
            // TODO: Dispatch unread message notifications once delivery channels are finalized.
            return CollaborationMessage::query()->create([
                'collaboration_id' => $collaboration->getKey(),
                'sender_id' => $sender?->getKey(),
                'message' => $message,
                'is_internal' => $isInternal,
            ]);
        });
    }

    public function addDocument(
        Collaboration $collaboration,
        array $documentData,
        ?User $user = null
    ): CollaborationDocument {
        return DB::transaction(function () use ($collaboration, $documentData, $user): CollaborationDocument {
            $documentData = array_intersect_key($documentData, array_flip([
                'document_type',
                'file_path',
                'original_name',
                'notes',
            ]));

            $documentData['collaboration_id'] = $collaboration->getKey();
            $documentData['uploaded_by'] = $user?->getKey();

            // TODO: Add file storage validation, access rules, and document classification checks.
            return CollaborationDocument::query()->create($documentData);
        });
    }

    public function scheduleVisit(
        Collaboration $collaboration,
        array $visitData,
        ?User $user = null
    ): CollaborationVisit {
        return DB::transaction(function () use ($collaboration, $visitData, $user): CollaborationVisit {
            $visitData = array_intersect_key($visitData, array_flip([
                'property_id',
                'client_id',
                'visit_date',
                'status',
                'feedback',
                'result',
            ]));

            $visitData['collaboration_id'] = $collaboration->getKey();
            $visitData['property_id'] ??= $collaboration->property_id;
            $visitData['client_id'] ??= $collaboration->client_id;
            $visitData['scheduled_by'] = $user?->getKey();
            $visitData['status'] ??= VisitStatus::SCHEDULED->value;

            // TODO: Check property calendar availability and participant conflicts.
            return CollaborationVisit::query()->create($visitData);
        });
    }

    public function submitOffer(
        Collaboration $collaboration,
        array $offerData,
        ?User $user = null
    ): CollaborationOffer {
        return DB::transaction(function () use ($collaboration, $offerData, $user): CollaborationOffer {
            $offerData = array_intersect_key($offerData, array_flip([
                'property_id',
                'client_id',
                'offer_number',
                'amount',
                'currency',
                'status',
                'submitted_at',
                'notes',
            ]));

            $offerData['collaboration_id'] = $collaboration->getKey();
            $offerData['property_id'] ??= $collaboration->property_id;
            $offerData['client_id'] ??= $collaboration->client_id;
            $offerData['submitted_by'] = $user?->getKey();
            $offerData['offer_number'] ??= $this->generateOfferNumber();
            $offerData['status'] ??= OfferStatus::SUBMITTED->value;
            $offerData['submitted_at'] ??= Carbon::now();

            // TODO: Validate offer amount against property pricing and negotiation rules.
            return CollaborationOffer::query()->create($offerData);
        });
    }

    public function acceptOffer(CollaborationOffer $offer, ?User $user = null): CollaborationOffer
    {
        return DB::transaction(function () use ($offer, $user): CollaborationOffer {
            // TODO: Decide whether accepting one offer cancels/rejects competing offers.
            $offer->fill([
                'status' => OfferStatus::ACCEPTED->value,
                'accepted_at' => Carbon::now(),
                'rejected_at' => null,
            ]);
            $offer->save();

            $this->createActivityLogPlaceholder($offer->collaboration, 'offer_accepted', $user);

            return $offer->refresh();
        });
    }

    public function rejectOffer(CollaborationOffer $offer, ?User $user = null): CollaborationOffer
    {
        return DB::transaction(function () use ($offer, $user): CollaborationOffer {
            // TODO: Capture offer rejection reason once the workflow supports it.
            $offer->fill([
                'status' => OfferStatus::REJECTED->value,
                'rejected_at' => Carbon::now(),
            ]);
            $offer->save();

            $this->createActivityLogPlaceholder($offer->collaboration, 'offer_rejected', $user);

            return $offer->refresh();
        });
    }

    public function syncCommissions(Collaboration $collaboration, array $commissions): void
    {
        DB::transaction(function () use ($collaboration, $commissions): void {
            $keptCommissionIds = [];

            foreach ($commissions as $commissionData) {
                if (! is_array($commissionData)) {
                    continue;
                }

                $commissionData = $this->onlyCommissionData($commissionData);

                if ($commissionData === []) {
                    continue;
                }

                $commissionData['collaboration_id'] = $collaboration->getKey();
                $commissionData['commission_type'] ??= CommissionType::PERCENT->value;
                $commissionData['status'] ??= 'pending';

                $lookup = [
                    'collaboration_id' => $collaboration->getKey(),
                    'agent_id' => $commissionData['agent_id'] ?? null,
                    'commission_role' => $commissionData['commission_role'] ?? null,
                ];

                $commission = CollaborationCommission::query()->updateOrCreate($lookup, $commissionData);
                $keptCommissionIds[] = $commission->getKey();
            }

            $staleQuery = CollaborationCommission::query()
                ->where('collaboration_id', $collaboration->getKey());

            if ($keptCommissionIds !== []) {
                $staleQuery->whereNotIn('id', $keptCommissionIds);
            }

            $staleQuery->delete();
        });
    }

    public function validateCommission(
        CollaborationCommission $commission,
        User $validator
    ): CollaborationCommission {
        return DB::transaction(function () use ($commission, $validator): CollaborationCommission {
            // TODO: Restrict validation to authorized accounting/manager roles.
            $commission->fill([
                'status' => 'validated',
                'validated_by' => $validator->getKey(),
                'validated_at' => Carbon::now(),
            ]);
            $commission->save();

            return $commission->refresh();
        });
    }

    public function generateCollaborationNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('COLLAB-%s-', $year);

        $lastNumber = Collaboration::query()
            ->where('collaboration_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('collaboration_number')
            ->value('collaboration_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    public function generateOfferNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('OFFER-%s-', $year);

        $lastNumber = CollaborationOffer::query()
            ->where('offer_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('offer_number')
            ->value('offer_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    public function canAccessOwnerDetails(Collaboration $collaboration, User $user): bool
    {
        if ($user->hasRole('Manager')) {
            return true;
        }

        if ($collaboration->status !== CollaborationStatus::ACCEPTED->value) {
            return false;
        }

        return in_array($user->getKey(), [
            $collaboration->requesting_agent_id,
            $collaboration->owner_agent_id,
        ], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function withoutRelationData(array $data): array
    {
        return array_diff_key($data, array_flip(self::RELATION_KEYS));
    }

    /**
     * @return array<string, mixed>
     */
    private function onlyCommissionData(array $commissionData): array
    {
        return array_intersect_key($commissionData, array_flip([
            'agent_id',
            'commission_role',
            'commission_type',
            'commission_value',
            'calculated_amount',
            'currency',
            'status',
            'validated_by',
            'validated_at',
            'notes',
        ]));
    }

    private function createActivityLogPlaceholder(
        ?Collaboration $collaboration,
        string $action,
        ?User $user = null
    ): void {
        if ($collaboration === null) {
            return;
        }

        // TODO: Replace this placeholder with a domain activity/event dispatcher.
        ActivityLog::query()->create([
            'agency_id' => $collaboration->agency_id,
            'user_id' => $user?->getKey(),
            'module' => 'collaboration',
            'action' => $action,
            'description' => sprintf('Collaboration %s', str_replace('_', ' ', $action)),
            'level' => 'info',
            'metadata' => [
                'collaboration_id' => $collaboration->getKey(),
                'collaboration_number' => $collaboration->collaboration_number,
                'notification_status' => NotificationStatus::UNREAD->value,
                'related_models' => [
                    Property::class,
                    Client::class,
                    AppNotification::class,
                ],
            ],
            'logged_at' => Carbon::now(),
        ]);
    }
}
