<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class ExpireOffers
{
    public function execute(): int
    {
        $expired = 0;

        Offer::published()
            ->where("end_date", "<", today())
            ->eachById(function (Offer $offer) use (&$expired): void {
                $offerToExpire = DB::transaction(fn(): ?Offer => $this->lockPublishedOffer($offer));

                if ($offerToExpire === null) {
                    return;
                }

                $offerToExpire->update(["status" => OfferStatus::Expired]);
                $expired++;

                activity()->causedByAnonymous()
                    ->performedOn($offerToExpire)
                    ->withProperties([
                        "company_id" => $offerToExpire->company_id,
                        "end_date" => $offerToExpire->end_date->toDateString(),
                        "new_status" => OfferStatus::Expired->value,
                    ])
                    ->log("offer_expired_automatically");
            });

        return $expired;
    }

    private function lockPublishedOffer(Offer $offer): ?Offer
    {
        $locked = Offer::whereKey($offer->getKey())->lockForUpdate()->first();

        return $locked?->status === OfferStatus::Published ? $locked : null;
    }
}
