<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanyInvitationStatus: string
{
    case Pending = "pending";
    case Accepted = "accepted";
    case Revoked = "revoked";
}
