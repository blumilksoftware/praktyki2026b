<?php

declare(strict_types=1);

namespace App\Enums;

enum PartnershipStatusFilter: string
{
    case Active = "active";
    case PendingIncoming = "pending_incoming";
    case PendingOutgoing = "pending_outgoing";
}
