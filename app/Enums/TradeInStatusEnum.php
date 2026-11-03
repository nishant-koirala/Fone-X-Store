<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TradeInStatusEnum: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case ACCEPTED = 'accepted';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Review',
            self::REVIEWED => 'Valuation Reviewed',
            self::ACCEPTED => 'Offer Accepted',
            self::COMPLETED => 'Exchange Completed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::REVIEWED => 'info',
            self::ACCEPTED => 'primary',
            self::COMPLETED => 'success',
        };
    }
}
