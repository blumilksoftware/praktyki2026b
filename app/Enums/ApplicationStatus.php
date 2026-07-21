<?php

declare(strict_types=1);

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = "pending";
    case Reviewed = "reviewed";
    case Accepted = "accepted";
    case Rejected = "rejected";

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Accepted, self::Rejected => true,
            default => false,
        };
    }

    public function canTransitionTo(self $targetStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($targetStatus, [self::Reviewed, self::Accepted, self::Rejected], true),
            self::Reviewed => in_array($targetStatus, [self::Accepted, self::Rejected], true),
            self::Accepted, self::Rejected => false,
        };
    }
}
