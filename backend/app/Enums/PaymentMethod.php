<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CHECK = 'check';
    case CARD = 'card';
    case MOBILE_PAYMENT = 'mobile_payment';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Espèces',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::CHECK => 'Chèque',
            self::CARD => 'Carte bancaire',
            self::MOBILE_PAYMENT => 'Paiement mobile',
            self::OTHER => 'Autre',
        };
    }
}
