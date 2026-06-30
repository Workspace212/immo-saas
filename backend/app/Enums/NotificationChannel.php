<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case INTERNAL = 'internal';
    case EMAIL = 'email';
    case SMS = 'sms';
    case WHATSAPP = 'whatsapp';
    case PUSH = 'push';

    public function label(): string
    {
        return match ($this) {
            self::INTERNAL => 'Interne',
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::WHATSAPP => 'WhatsApp',
            self::PUSH => 'Push',
        };
    }
}
