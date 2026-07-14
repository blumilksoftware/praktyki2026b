<?php

declare(strict_types=1);

namespace App\Events\Offer;

use App\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;

class OfferBecameUnavailable
{
    use Dispatchable;

    public function __construct(
        public readonly Offer $offer,
        public readonly string $reason,
    ) {}
}
