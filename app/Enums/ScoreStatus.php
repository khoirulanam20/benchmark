<?php

namespace App\Enums;

enum ScoreStatus: string
{
    case Pending = 'pending';
    case Scored = 'scored';
    case Validated = 'validated';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Scored => 'Scored',
            self::Validated => 'Validated',
        };
    }
}
