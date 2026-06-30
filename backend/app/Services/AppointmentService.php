<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AvailabilityType;
use App\Enums\ReminderStatus;
use App\Enums\ReminderType;
use App\Models\Appointment;
use App\Models\AppointmentParticipant;
use App\Models\AppointmentReminder;
use App\Models\Client;
use App\Models\Collaboration;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Mandate;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAvailability;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'participants',
        'reminders',
    ];

    public function createAppointment(array $data, ?User $user = null): Appointment
    {
        return DB::transaction(function () use ($data, $user): Appointment {
            $participants = $data['participants'] ?? null;
            $reminders = $data['reminders'] ?? null;

            $appointmentData = $this->withoutRelationData($data);
            $appointmentData['appointment_number'] ??= $this->generateAppointmentNumber();

            if ($user !== null) {
                $appointmentData['created_by'] = $user->getKey();
            }

            // TODO: Validate appointment type, priority, status transitions, and agency-level scheduling rules.
            $appointment = Appointment::query()->create($appointmentData);

            if (is_array($participants)) {
                $this->syncParticipants($appointment, $participants);
            }

            if (is_array($reminders)) {
                foreach ($reminders as $reminderData) {
                    if (is_array($reminderData)) {
                        $this->createReminder($appointment, $reminderData);
                    }
                }
            }

            if ($appointment->property_id !== null) {
                $this->createPropertyAvailability($appointment);
            }

            return $appointment->refresh();
        });
    }

    public function updateAppointment(Appointment $appointment, array $data, ?User $user = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $data, $user): Appointment {
            $appointmentData = $this->withoutRelationData($data);

            if ($appointmentData !== []) {
                // TODO: Enforce allowed status/date changes by role and appointment lifecycle.
                $appointment->fill($appointmentData);
                $appointment->save();
            }

            if (array_key_exists('participants', $data) && is_array($data['participants'])) {
                $this->syncParticipants($appointment, $data['participants']);
            }

            if (array_key_exists('reminders', $data) && is_array($data['reminders'])) {
                AppointmentReminder::query()
                    ->where('appointment_id', $appointment->getKey())
                    ->delete();

                foreach ($data['reminders'] as $reminderData) {
                    if (is_array($reminderData)) {
                        $this->createReminder($appointment, $reminderData);
                    }
                }
            }

            // TODO: Sync related property availability when appointment date/property changes.
            // TODO: Persist updater metadata when the appointments table supports it.

            return $appointment->refresh();
        });
    }

    public function cancelAppointment(Appointment $appointment, ?User $user = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $user): Appointment {
            // TODO: Notify participants and cancel pending reminders once notification rules are defined.
            $appointment->fill(['status' => 'cancelled']);
            $appointment->save();

            AppointmentReminder::query()
                ->where('appointment_id', $appointment->getKey())
                ->where('status', ReminderStatus::PENDING->value)
                ->update(['status' => ReminderStatus::CANCELLED->value]);

            // TODO: Persist cancellation actor metadata when the appointments table supports it.

            return $appointment->refresh();
        });
    }

    public function completeAppointment(Appointment $appointment, ?User $user = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $user): Appointment {
            // TODO: Capture outcome notes and trigger follow-up tasks when the workflow is defined.
            $appointment->fill(['status' => 'completed']);
            $appointment->save();

            // TODO: Persist completion actor metadata when the appointments table supports it.

            return $appointment->refresh();
        });
    }

    public function markNoShow(Appointment $appointment, ?User $user = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $user): Appointment {
            // TODO: Decide whether no-show applies to the whole appointment or individual participants.
            $appointment->fill(['status' => 'no_show']);
            $appointment->save();

            // TODO: Persist no-show actor metadata when the appointments table supports it.

            return $appointment->refresh();
        });
    }

    public function syncParticipants(Appointment $appointment, array $participants): void
    {
        DB::transaction(function () use ($appointment, $participants): void {
            $keptParticipantIds = [];

            foreach ($participants as $participantData) {
                if (! is_array($participantData)) {
                    continue;
                }

                $participantData = $this->onlyParticipantData($participantData);

                if ($participantData === []) {
                    continue;
                }

                $lookup = $this->participantLookup($appointment, $participantData);
                $participant = AppointmentParticipant::query()->updateOrCreate($lookup, $participantData);
                $keptParticipantIds[] = $participant->getKey();
            }

            $staleQuery = AppointmentParticipant::query()
                ->where('appointment_id', $appointment->getKey());

            if ($keptParticipantIds !== []) {
                $staleQuery->whereNotIn('id', $keptParticipantIds);
            }

            $staleQuery->delete();
        });
    }

    public function createReminder(Appointment $appointment, array $reminderData): AppointmentReminder
    {
        $reminderData = array_intersect_key($reminderData, array_flip([
            'user_id',
            'reminder_type',
            'remind_at',
            'status',
            'sent_at',
            'channel',
            'error_message',
        ]));

        $reminderData['appointment_id'] = $appointment->getKey();
        $reminderData['reminder_type'] ??= ReminderType::CUSTOM->value;
        $reminderData['status'] ??= ReminderStatus::PENDING->value;
        $reminderData['channel'] ??= 'notification';

        return AppointmentReminder::query()->create($reminderData);
    }

    public function createDefaultReminders(Appointment $appointment): void
    {
        DB::transaction(function () use ($appointment): void {
            $startAt = Carbon::parse($appointment->start_at);

            $this->createReminder($appointment, [
                'user_id' => $appointment->created_by,
                'reminder_type' => ReminderType::BEFORE_1_DAY->value,
                'remind_at' => $startAt->copy()->subDay(),
            ]);

            $this->createReminder($appointment, [
                'user_id' => $appointment->created_by,
                'reminder_type' => ReminderType::BEFORE_1_HOUR->value,
                'remind_at' => $startAt->copy()->subHour(),
            ]);
        });
    }

    public function createPropertyAvailability(Appointment $appointment): ?PropertyAvailability
    {
        if ($appointment->property_id === null) {
            return null;
        }

        // TODO: Link availability rows to appointments if the schema later supports that relationship.
        return PropertyAvailability::query()->create([
            'property_id' => $appointment->property_id,
            'created_by' => $appointment->created_by,
            'availability_type' => AvailabilityType::VISIT_SCHEDULED->value,
            'start_at' => $appointment->start_at,
            'end_at' => $appointment->end_at,
            'title' => $appointment->title,
            'status' => 'active',
        ]);
    }

    public function generateAppointmentNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('APT-%s-', $year);

        $lastNumber = Appointment::query()
            ->where('appointment_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('appointment_number')
            ->value('appointment_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    public function hasTimeConflict(User $user, string $startAt, string $endAt, ?int $ignoreAppointmentId = null): bool
    {
        $query = Appointment::query()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where(function ($query) use ($user): void {
                $query->where('created_by', $user->getKey())
                    ->orWhereExists(function ($subQuery) use ($user): void {
                        $subQuery->selectRaw('1')
                            ->from('appointment_participants')
                            ->whereColumn('appointment_participants.appointment_id', 'appointments.id')
                            ->where('appointment_participants.user_id', $user->getKey())
                            ->whereNull('appointment_participants.deleted_at');
                    });
            })
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt);

        if ($ignoreAppointmentId !== null) {
            $query->whereKeyNot($ignoreAppointmentId);
        }

        return $query->exists();
    }

    public function propertyIsAvailable(Property $property, string $startAt, string $endAt): bool
    {
        $blockingTypes = [
            AvailabilityType::RESERVED->value,
            AvailabilityType::OCCUPIED->value,
            AvailabilityType::MAINTENANCE->value,
            AvailabilityType::BLOCKED->value,
            AvailabilityType::VISIT_SCHEDULED->value,
        ];

        return ! PropertyAvailability::query()
            ->where('property_id', $property->getKey())
            ->whereIn('availability_type', $blockingTypes)
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();
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
    private function onlyParticipantData(array $participantData): array
    {
        return array_intersect_key($participantData, array_flip([
            'user_id',
            'client_id',
            'owner_id',
            'provider_id',
            'participant_type',
            'name',
            'email',
            'phone',
            'status',
            'notes',
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function participantLookup(Appointment $appointment, array $participantData): array
    {
        $lookup = [
            'appointment_id' => $appointment->getKey(),
            'participant_type' => $participantData['participant_type'] ?? 'guest',
        ];

        foreach (['user_id', 'client_id', 'owner_id', 'provider_id', 'email', 'phone', 'name'] as $key) {
            if (! empty($participantData[$key])) {
                $lookup[$key] = $participantData[$key];

                return $lookup;
            }
        }

        return $lookup;
    }
}
