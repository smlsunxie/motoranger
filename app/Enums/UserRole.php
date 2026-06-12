<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Operator = 'operator';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => '系統管理員',
            self::Manager => '店長',
            self::Operator => '技師',
        };
    }
}
