<?php

namespace App\Enums;

enum ClaimStage: string
{
    case Reporting = 'reporting';
    case Assessment = 'assessment';
    case Review = 'review';
    case Decision = 'decision';
    case Payment = 'payment';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Reporting => 'Reporting',
            self::Assessment => 'Assessment',
            self::Review => 'Review',
            self::Decision => 'Decision',
            self::Payment => 'Payment',
            self::Closed => 'Closed',
        };
    }
}
