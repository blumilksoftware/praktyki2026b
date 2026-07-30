<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\DTO\Offer\SearchOffersData;
use App\Enums\VerificationStatus;
use App\Models\Offer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchOffers
{
    /**
     * @return array{
     *     offers: LengthAwarePaginator,
     *     mapPoints: Collection<int, array{id: string, title: string, latitude: float, longitude: float}>,
     * }
     */
    public function execute(SearchOffersData $data): array
    {
        $offers = Offer::query()
            ->published()
            ->withRemainingSpots()
            ->forStudyFields($data->studyFieldIds)
            ->forWorkMode($data->workMode)
            ->forCity($data->city)
            ->forDateRange($data->dateFrom, $data->dateTo, $data->dateFlexDays);

        $paginatedOffers = (clone $offers)
            ->with(["company", "applications"])
            ->orderByDesc("published_at")
            ->paginate($data->perPage)
            ->withQueryString()
            ->through(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "city" => $offer->city,
                "work_mode" => $offer->work_mode->value,
                "start_date" => $offer->start_date->toDateString(),
                "end_date" => $offer->end_date->toDateString(),
                "spots" => $offer->spots,
                "remaining_spots" => max(0, $offer->spots - $offer->applications->count()),
                "company" => [
                    "name" => $offer->company->name,
                    "logo_path" => $offer->company->logo_path,
                    "is_verified" => ($offer->company->verification_status ?? null) === VerificationStatus::Verified,
                ],
            ]);

        $mapPoints = (clone $offers)
            ->get(["id", "title", "latitude", "longitude"])
            ->map(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "latitude" => $offer->latitude,
                "longitude" => $offer->longitude,
            ]);

        return [
            "offers" => $paginatedOffers,
            "mapPoints" => $mapPoints,
        ];
    }
}
