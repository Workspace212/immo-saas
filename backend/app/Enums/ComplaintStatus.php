<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case NEW = 'new';
    case SEEN = 'seen';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case WAITING_PROVIDER = 'waiting_provider';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Nouvelle',
            self::SEEN => 'Vue',
            self::ASSIGNED => 'Assignée',
            self::IN_PROGRESS => 'En cours',
            self::WAITING_PROVIDER => 'En attente du prestataire',
            self::RESOLVED => 'Résolue',
            self::CLOSED => 'Clôturée',
        };
    }
}
