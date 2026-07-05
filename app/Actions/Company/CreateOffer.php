<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTO\Offer\CreateOfferData;
use App\Enums\OfferStatus;
use App\Enums\VerificationStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Services\MapboxGeocodingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CreateOffer
{
    public function __construct(
        private readonly MapboxGeocodingService $geocodingService,
    ) {}

    public function execute(Company $company, CreateOfferData $data): Offer
    {
        if ($data->status === OfferStatus::Published && $company->verification_status !== VerificationStatus::Verified) {
            throw ValidationException::withMessages([
                "status" => __("validation.offer_publish_requires_verification"),
            ]);
        }

        try {
            $coordinates = $this->geocodingService->geocode($data->city);
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                "city" => __("validation.city_geocoding_failed"),
            ]);
        }

        return DB::transaction(function () use ($company, $data, $coordinates): Offer {
            $offer = Offer::create([
                "company_id" => $company->id,
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
            ]);

            $offer->studyFields()->sync($data->studyFieldIds);
            $offer->universities()->sync($data->universityIds);

            return $offer;
        });
    }
}
