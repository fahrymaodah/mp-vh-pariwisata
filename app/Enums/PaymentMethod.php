<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case BankTransfer = 'bank_transfer';
    case CityLedger = 'city_ledger';
    case Voucher = 'voucher';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::CreditCard => 'Credit Card',
            self::BankTransfer => 'Bank Transfer',
            self::CityLedger => 'City Ledger',
            self::Voucher => 'Voucher',
        };
    }
}
