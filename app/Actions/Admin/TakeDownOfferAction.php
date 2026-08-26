<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\OfferStatus;
use App\Models\Offer;
use App\Models\User;

class TakeDownOfferAction
{
    public function execute(Offer $offer, User $admin): void
    {
        $oldStatus = $offer->status;

        $offer->update(["status" => OfferStatus::Closed]);

        activity()->causedBy($admin)
            ->performedOn($offer)
            ->withProperties([
                "old_status" => $oldStatus->value,
                "new_status" => OfferStatus::Closed->value,
            ])
            ->log("offer_taken_down");
    }
}
