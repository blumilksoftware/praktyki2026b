<?php

declare(strict_types=1);

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = "pending";
    case Reviewed = "reviewed";
    case Accepted = "accepted";
    case Rejected = "rejected";
}
