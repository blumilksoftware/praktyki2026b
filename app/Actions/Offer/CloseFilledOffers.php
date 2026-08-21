<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class CloseFilledOffers
{
    public function execute(): int
    {
        $closed = 0;

        Offer::published()
            ->withoutRemainingSpots()
            ->eachById(function (Offer $offer) use (&$closed): void {
                $offerToClose = DB::transaction(fn(): ?Offer => $this->lockFilledOffer($offer));

                if ($offerToClose === null) {
                    return;
                }

                $offerToClose->update(["status" => OfferStatus::Closed]);
                $closed++;

                activity()->causedByAnonymous()
                    ->performedOn($offerToClose)
                    ->withProperties([
                        "company_id" => $offerToClose->company_id,
                        "spots" => $offerToClose->spots,
                        "new_status" => OfferStatus::Closed->value,
                    ])
                    ->log("offer_closed_automatically");
            });

        return $closed;
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
