<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTO\Offer\UpdateOfferData;
use App\Enums\OfferStatus;
use App\Enums\VerificationStatus;
use App\Models\Offer;
use App\Services\MapboxGeocodingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UpdateOffer
{
    public function __construct(
        private readonly MapboxGeocodingService $geocodingService,
    ) {}

    public function execute(Offer $offer, UpdateOfferData $data): Offer
    {
        if ($data->status === OfferStatus::Published && $offer->company->verification_status !== VerificationStatus::Verified) {
            throw ValidationException::withMessages([
                "status" => __("validation.offer_publish_requires_verification"),
            ]);
        }

        $coordinates = ["latitude" => $offer->latitude, "longitude" => $offer->longitude];

        if ($data->city !== $offer->city) {
            try {
                $coordinates = $this->geocodingService->geocode($data->city);
            } catch (RuntimeException) {
                throw ValidationException::withMessages([
                    "city" => __("validation.city_geocoding_failed"),
                ]);
            }
        }

        return DB::transaction(function () use ($offer, $data, $coordinates): Offer {
            $offer->update([
                "title" => $data->title,
                "description" => $data->description,
                "spots" => $data->spots,
                "city" => $data->city,
                "latitude" => $coordinates["latitude"],
                "longitude" => $coordinates["longitude"],
                "start_date" => $data->startDate,
                "end_date" => $data->endDate,
                "work_mode" => $data->workMode,
                "status" => $data->status,
                "is_paid" => $data->isPaid,
                "salary_min" => $data->salaryMin,
                "salary_max" => $data->salaryMax,
            ]);

            $offer->studyFields()->sync($data->studyFieldIds);
            $offer->universities()->sync($data->universityIds);

            return $offer->fresh();
        });
    }
}
