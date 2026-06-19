<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RepairOrderStatus: string implements HasColor, HasLabel
{
    case Quote = 'quote';
    case Approved = 'approved';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Quote => '估價中',
            self::Approved => '已確認',
            self::InProgress => '維修中',
            self::Completed => '已完工',
            self::Delivered => '已交車',
            self::Cancelled => '已取消',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Quote => 'gray',
            self::Approved => 'info',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }
}
