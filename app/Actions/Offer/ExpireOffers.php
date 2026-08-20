<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class ExpireOffers
{
    public function execute(): void
    {
        Offer::published()
            ->where("end_date", "<", today())
            ->eachById(function (Offer $offer): void {
                $offerToExpire = DB::transaction(fn(): ?Offer => $this->lockPublishedOffer($offer));

                $offerToExpire?->update(["status" => OfferStatus::Expired]);
            });
    }

    private function lockPublishedOffer(Offer $offer): ?Offer
    {
        $locked = Offer::whereKey($offer->getKey())->lockForUpdate()->first();

        return $locked?->status === OfferStatus::Published ? $locked : null;
    }
}
