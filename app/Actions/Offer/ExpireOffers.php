<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\OfferStatus;
use App\Models\Offer;

class ExpireOffers
{
    public function execute(): void
    {
        Offer::published()
            ->where("end_date", "<", now())
            ->each(fn(Offer $offer): bool => $offer->update(["status" => OfferStatus::Expired]));
    }
}
