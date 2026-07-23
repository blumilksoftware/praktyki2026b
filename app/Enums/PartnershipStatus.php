<?php

declare(strict_types=1);

namespace App\Enums;

enum PartnershipStatus: string
{
    case Pending = "pending";
    case Active = "active";
    case Suspended = "suspended";
}
