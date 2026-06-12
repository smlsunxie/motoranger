<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VehicleType: string implements HasLabel
{
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Motorcycle => '機車',
            self::Car => '汽車',
            self::Other => '其他',
        };
    }
}
