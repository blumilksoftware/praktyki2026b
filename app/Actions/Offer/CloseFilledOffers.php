<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class CloseFilledOffers
{
    public function execute(): void
    {
        Offer::published()
            ->withoutRemainingSpots()
            ->eachById(function (Offer $offer): void {
                $offerToClose = DB::transaction(fn(): ?Offer => $this->lockFilledOffer($offer));

                $offerToClose?->update(["status" => OfferStatus::Closed]);
            });
    }

    private function lockFilledOffer(Offer $offer): ?Offer
    {
        $locked = Offer::whereKey($offer->getKey())->lockForUpdate()->first();

        if ($locked === null || $locked->status !== OfferStatus::Published || $locked->remainingSpots() > 0) {
            return null;
        }

        return $locked;
    }
}
