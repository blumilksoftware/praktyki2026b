<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\OfferStatus;
use App\Events\Offer\OfferBecameUnavailable;
use App\Models\Offer;

class OfferObserver
{
    public function saving(Offer $offer): void
    {
        if ($offer->status === OfferStatus::Published && $offer->published_at === null) {
            $offer->published_at = now();
        }
    }

    public function updated(Offer $offer): void
    {
        if (!$offer->wasChanged("status")) {
            return;
        }

        $reason = match ($offer->status) {
            OfferStatus::Closed => "closed",
            OfferStatus::Expired => "expired",
            default => null,
        };

        if ($reason !== null) {
            OfferBecameUnavailable::dispatch($offer, $reason);
        }
    }

    public function deleted(Offer $offer): void
    {
        OfferBecameUnavailable::dispatch($offer, "deleted");
    }
}
