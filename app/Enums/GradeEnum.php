<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GradeEnum: string implements HasColor, HasLabel
{
    case NEW = 'new';
    case A = 'A';
    case B = 'B';
    case C = 'C';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Brand New',
            self::A => 'Grade A (Pristine / Like New)',
            self::B => 'Grade B (Good / Minor Wear)',
            self::C => 'Grade C (Fair / Heavily Used)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NEW => 'success',
            self::A => 'info',
            self::B => 'warning',
            self::C => 'gray',
        };
    }
}
