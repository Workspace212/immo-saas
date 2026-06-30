<?php

namespace App\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case DOCUMENT = 'document';
    case IMAGE = 'image';
    case SYSTEM = 'system';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Texte',
            self::DOCUMENT => 'Document',
            self::IMAGE => 'Image',
            self::SYSTEM => 'Système',
            self::OTHER => 'Autre',
        };
    }
}
