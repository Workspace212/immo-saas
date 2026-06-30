<?php

namespace App\Enums;

enum ComplaintTypeEnum: string
{
    case PLUMBING = 'plumbing';
    case ELECTRICITY = 'electricity';
    case AIR_CONDITIONING = 'air_conditioning';
    case PAINTING = 'painting';
    case LOCKSMITH = 'locksmith';
    case INTERNET = 'internet';
    case CLEANING = 'cleaning';
    case ELEVATOR = 'elevator';
    case SECURITY = 'security';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PLUMBING => 'Plomberie',
            self::ELECTRICITY => 'Électricité',
            self::AIR_CONDITIONING => 'Climatisation',
            self::PAINTING => 'Peinture',
            self::LOCKSMITH => 'Serrurerie',
            self::INTERNET => 'Internet',
            self::CLEANING => 'Nettoyage',
            self::ELEVATOR => 'Ascenseur',
            self::SECURITY => 'Sécurité',
            self::OTHER => 'Autre',
        };
    }
}
