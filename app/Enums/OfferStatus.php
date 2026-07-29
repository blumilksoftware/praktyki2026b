<?php

declare(strict_types=1);

namespace App\Enums;

enum OfferStatus: string
{
    case Draft = "draft";
    case Published = "published";
    case Closed = "closed";
    case Expired = "expired";

    public static function sortOrder(): array
    {
        return [self::Draft, self::Published, self::Expired, self::Closed];
    }
}
