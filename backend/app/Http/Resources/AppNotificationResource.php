<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\NotificationQueue;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppNotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'user_id' => $this->user_id,
            'created_by' => $this->created_by,
            'notification_number' => $this->notification_number,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'body' => $this->message,
            'data' => $data,
            'priority' => $this->priority,
            'status' => $this->status,
            'read_at' => $this->read_at,
            'action_url' => $this->action_url,
            'related_type' => $this->related_type,
            'related_id' => $this->related_id,
            'notification' => [
                'number' => $this->notification_number,
                'title' => $this->title,
                'body' => $this->message,
                'type' => $this->type,
                'priority' => $this->priority,
                'status' => $this->status,
            ],
            'creator' => $this->whenLoaded('creator'),
            'recipient' => $this->whenLoaded('user'),
            'template' => $this->template($data),
            'queue' => $this->queueItems($data),
            'logs' => [],
            'metadata' => [
                'channel' => $data['channel'] ?? null,
                'template_id' => $data['notification_template_id'] ?? null,
                'payload' => $data['payload'] ?? $data,
                'related' => [
                    'type' => $this->related_type,
                    'id' => $this->related_id,
                ],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function template(array $data): mixed
    {
        $templateId = $data['notification_template_id'] ?? null;

        if ($templateId === null) {
            return null;
        }

        return NotificationTemplate::query()->find($templateId);
    }

    private function queueItems(array $data): array
    {
        return NotificationQueue::query()
            ->where('user_id', $this->user_id)
            ->where(function ($query) use ($data): void {
                $query->where('subject', $this->title)
                    ->orWhere('body', $this->message);

                if (isset($data['notification_template_id'])) {
                    $query->orWhere('notification_template_id', $data['notification_template_id']);
                }
            })
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }
}
