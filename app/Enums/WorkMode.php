<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkMode: string
{
    case OnSite = "onSite";
    case Hybrid = "hybrid";
    case Remote = "remote";
}
