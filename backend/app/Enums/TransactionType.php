<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::DEBIT => 'Débit',
            self::CREDIT => 'Crédit',
            self::TRANSFER => 'Transfert',
        };
    }
}
