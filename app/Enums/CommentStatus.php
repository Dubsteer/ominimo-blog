<?php

namespace App\Enums;

enum CommentStatus: string
{
    case Active = 'active';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Hidden => 'Hidden',
        };
    }
}
