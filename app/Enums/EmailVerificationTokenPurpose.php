<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailVerificationTokenPurpose: string
{
    case Registration = "registration";
    case EmailChange = "email_change";
}
