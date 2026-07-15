<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\OfferStatus;
use App\Enums\VerificationStatus;
use App\Models\Offer;
use Illuminate\Validation\ValidationException;

class PublishOffer
{
    public function execute(Offer $offer): Offer
    {
        if ($offer->status !== OfferStatus::Draft) {
            throw ValidationException::withMessages([
                "status" => __("validation.offer_publish_invalid_status"),
            ]);
        }

        if ($offer->company->verification_status !== VerificationStatus::Verified) {
            throw ValidationException::withMessages([
                "status" => __("validation.offer_publish_requires_verification"),
            ]);
        }

        $offer->update(["status" => OfferStatus::Published]);

        return $offer;
    }
}
