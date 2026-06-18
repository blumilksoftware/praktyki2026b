<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case Pending = "pending";
    case Active = "active";
    case Blocked = "blocked";
    case Deleted = "deleted";
}
