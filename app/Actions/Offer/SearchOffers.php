<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\DTO\Offer\SearchOffersData;
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
            ->with("company")
            ->orderByDesc("published_at")
            ->paginate($data->perPage)
            ->withQueryString()
            ->through(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "company_name" => $offer->company->name,
                "city" => $offer->city,
                "start_date" => $offer->start_date->toDateString(),
                "end_date" => $offer->end_date->toDateString(),
                "work_mode" => $offer->work_mode->value,
                "spots" => $offer->spots,
                "is_paid" => $offer->is_paid,
                "salary_min" => $offer->salary_min,
                "salary_max" => $offer->salary_max,
                "published_at" => $offer->published_at?->toIso8601String(),
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
