<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case UNREAD = 'unread';
    case READ = 'read';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::SENT => 'Envoyée',
            self::FAILED => 'Échec',
            self::CANCELLED => 'Annulée',
            self::UNREAD => 'Non lue',
            self::READ => 'Lue',
            self::ARCHIVED => 'Archivée',
        };
    }
}
