<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Card = 'card';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => '現金',
            self::Transfer => '轉帳',
            self::Card => '刷卡',
            self::Other => '其他',
        };
    }
}
