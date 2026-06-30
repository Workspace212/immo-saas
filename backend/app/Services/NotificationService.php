<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Agency;
use App\Models\AppNotification;
use App\Models\NotificationPreference;
use App\Models\NotificationQueue;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function notifyUser(User $user, array $data, ?User $createdBy = null): AppNotification
    {
        return DB::transaction(function () use ($user, $data, $createdBy): AppNotification {
            $notificationData = array_intersect_key($data, array_flip([
                'title',
                'message',
                'type',
                'priority',
                'action_url',
                'data',
                'related_type',
                'related_id',
            ]));

            $notificationData['user_id'] = $user->getKey();
            $notificationData['agency_id'] = $data['agency_id'] ?? $this->agencyIdFor($user);
            $notificationData['created_by'] = $createdBy?->getKey();
            $notificationData['notification_number'] = $data['notification_number'] ?? $this->generateNotificationNumber();
            $notificationData['priority'] ??= 'normal';
            $notificationData['status'] = NotificationStatus::UNREAD->value;

            // TODO: Apply notification throttling, deduplication, and priority escalation rules.
            return AppNotification::query()->create($notificationData);
        });
    }

    /**
     * @return array<int, AppNotification>
     */
    public function notifyUsers(array $users, array $data, ?User $createdBy = null): array
    {
        return DB::transaction(function () use ($users, $data, $createdBy): array {
            $notifications = [];

            foreach ($users as $user) {
                if ($user instanceof User) {
                    $notifications[] = $this->notifyUser($user, $data, $createdBy);
                }
            }

            return $notifications;
        });
    }

    public function markAsRead(AppNotification $notification): AppNotification
    {
        return DB::transaction(function () use ($notification): AppNotification {
            $notification->fill([
                'status' => NotificationStatus::READ->value,
                'read_at' => Carbon::now(),
            ]);
            $notification->save();

            return $notification->refresh();
        });
    }

    public function markAsUnread(AppNotification $notification): AppNotification
    {
        return DB::transaction(function () use ($notification): AppNotification {
            $notification->fill([
                'status' => NotificationStatus::UNREAD->value,
                'read_at' => null,
            ]);
            $notification->save();

            return $notification->refresh();
        });
    }

    public function archive(AppNotification $notification): AppNotification
    {
        return DB::transaction(function () use ($notification): AppNotification {
            // TODO: Decide whether archived notifications should preserve read/unread history separately.
            $notification->fill([
                'status' => NotificationStatus::ARCHIVED->value,
            ]);
            $notification->save();

            return $notification->refresh();
        });
    }

    public function queueNotification(array $data): NotificationQueue
    {
        return DB::transaction(function () use ($data): NotificationQueue {
            $queueData = array_intersect_key($data, array_flip([
                'channel',
                'recipient',
                'subject',
                'body',
                'payload',
                'scheduled_at',
                'user_id',
                'agency_id',
                'notification_template_id',
            ]));

            $queueData['channel'] ??= NotificationChannel::INTERNAL->value;
            $queueData['status'] = NotificationStatus::PENDING->value;
            $queueData['attempts'] = $data['attempts'] ?? 0;

            // TODO: Validate recipient format per channel and apply quiet-hour scheduling.
            return NotificationQueue::query()->create($queueData);
        });
    }

    public function sendQueued(NotificationQueue $queue): NotificationQueue
    {
        return DB::transaction(function () use ($queue): NotificationQueue {
            $queue->fill([
                'status' => 'processing',
                'attempts' => ((int) $queue->attempts) + 1,
            ]);
            $queue->save();

            // TODO: Implement actual email, SMS, WhatsApp, push, and internal sending.
            $queue->fill([
                'status' => NotificationStatus::SENT->value,
                'sent_at' => Carbon::now(),
                'failed_at' => null,
                'error_message' => null,
            ]);
            $queue->save();

            return $queue->refresh();
        });
    }

    public function markQueueFailed(NotificationQueue $queue, string $error): NotificationQueue
    {
        return DB::transaction(function () use ($queue, $error): NotificationQueue {
            // TODO: Add retry/backoff rules and dead-letter handling.
            $queue->fill([
                'status' => NotificationStatus::FAILED->value,
                'failed_at' => Carbon::now(),
                'error_message' => $error,
            ]);
            $queue->save();

            return $queue->refresh();
        });
    }

    /**
     * @return array{title: string, body: string}
     */
    public function renderTemplate(NotificationTemplate $template, array $variables = []): array
    {
        return [
            'title' => $this->replaceTemplateVariables((string) $template->title_template, $variables),
            'body' => $this->replaceTemplateVariables((string) $template->body_template, $variables),
        ];
    }

    public function userWantsChannel(User $user, string $notificationType, string $channel): bool
    {
        $preference = NotificationPreference::query()
            ->where('user_id', $user->getKey())
            ->where('notification_type', $notificationType)
            ->first();

        $column = match ($channel) {
            NotificationChannel::INTERNAL->value => 'internal_enabled',
            NotificationChannel::EMAIL->value => 'email_enabled',
            NotificationChannel::SMS->value => 'sms_enabled',
            NotificationChannel::WHATSAPP->value => 'whatsapp_enabled',
            NotificationChannel::PUSH->value => 'push_enabled',
            default => null,
        };

        if ($column !== null && $preference instanceof NotificationPreference) {
            return (bool) $preference->{$column};
        }

        return in_array($channel, [
            NotificationChannel::INTERNAL->value,
            NotificationChannel::PUSH->value,
        ], true);
    }

    public function createTemplate(array $data): NotificationTemplate
    {
        return DB::transaction(function () use ($data): NotificationTemplate {
            $templateData = array_intersect_key($data, array_flip([
                'agency_id',
                'template_key',
                'channel',
                'title_template',
                'body_template',
                'is_active',
            ]));

            $templateData['channel'] ??= NotificationChannel::INTERNAL->value;
            $templateData['is_active'] ??= true;

            // TODO: Validate template keys and required variables by notification type.
            return NotificationTemplate::query()->create($templateData);
        });
    }

    public function updatePreference(
        User $user,
        string $notificationType,
        array $preferences
    ): NotificationPreference {
        return DB::transaction(function () use ($user, $notificationType, $preferences): NotificationPreference {
            $preferenceData = array_intersect_key($preferences, array_flip([
                'internal_enabled',
                'email_enabled',
                'sms_enabled',
                'whatsapp_enabled',
                'push_enabled',
            ]));

            // TODO: Enforce mandatory legal/account-security notifications regardless of preferences.
            return NotificationPreference::query()->updateOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'notification_type' => $notificationType,
                ],
                $preferenceData
            );
        });
    }

    public function generateNotificationNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('NOTIF-%s-', $year);

        $lastNumber = AppNotification::query()
            ->where('notification_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('notification_number')
            ->value('notification_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    private function replaceTemplateVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value) ?: '';
            }

            $template = str_replace(
                sprintf('{{%s}}', (string) $key),
                (string) $value,
                $template
            );
        }

        return $template;
    }

    private function agencyIdFor(User $user): ?int
    {
        if ($user->relationLoaded('agency') && $user->agency instanceof Agency) {
            return (int) $user->agency->getKey();
        }

        if ($user->agency_id === null) {
            return null;
        }

        return (int) $user->agency_id;
    }
}
