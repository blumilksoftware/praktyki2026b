<?php

declare(strict_types=1);

namespace App\Enums;

enum UniversityVerificationStatus: string
{
    case Pending = "pending";
    case Verified = "verified";
    case Rejected = "rejected";
}
