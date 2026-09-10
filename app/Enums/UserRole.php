<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Moderator = 'moderator';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Moderator => 'Moderator',
            self::Administrator => 'Administrator',
        };
    }

    public function canModerateContent(): bool
    {
        return $this === self::Moderator || $this === self::Administrator;
    }
}
