<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RepairItemType: string implements HasLabel
{
    case Part = 'part';
    case Labor = 'labor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Part => '零件',
            self::Labor => '工資',
        };
    }
}
