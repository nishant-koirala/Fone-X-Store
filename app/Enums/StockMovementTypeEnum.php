<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockMovementTypeEnum: string implements HasColor, HasLabel
{
    case RESTOCK = 'restock';
    case SALE = 'sale';
    case EXCHANGE_IN = 'exchange_in';
    case ADJUSTMENT = 'adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESTOCK => 'Restock',
            self::SALE => 'Sale',
            self::EXCHANGE_IN => 'Trade-In Exchange',
            self::ADJUSTMENT => 'Manual Adjustment',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::RESTOCK, self::EXCHANGE_IN => 'success',
            self::SALE => 'danger',
            self::ADJUSTMENT => 'warning',
        };
    }
}
