<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DeviceUnitStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
    case DEFECTIVE = 'defective';
    case RETURNED = 'returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::AVAILABLE => 'In Stock (Available)',
            self::RESERVED => 'Reserved',
            self::SOLD => 'Sold',
            self::DEFECTIVE => 'Defective / Inactive',
            self::RETURNED => 'Customer Returned',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::RESERVED => 'warning',
            self::SOLD => 'gray',
            self::DEFECTIVE => 'danger',
            self::RETURNED => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::AVAILABLE => 'heroicon-m-check-circle',
            self::RESERVED => 'heroicon-m-clock',
            self::SOLD => 'heroicon-m-shopping-bag',
            self::DEFECTIVE => 'heroicon-m-x-circle',
            self::RETURNED => 'heroicon-m-arrow-path',
        };
    }
}
