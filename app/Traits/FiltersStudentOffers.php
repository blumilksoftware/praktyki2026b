<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\VerificationStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait FiltersStudentOffers
{
    protected function buildFilteredOffersQuery(array $filters): Builder
    {
        $lat = isset($filters["latitude"]) ? (float)$filters["latitude"] : null;
        $lng = isset($filters["longitude"]) ? (float)$filters["longitude"] : null;
        $radius = isset($filters["radius_km"]) ? (float)$filters["radius_km"] : null;

        $hasRadiusFilter = $lat !== null && $lng !== null && $radius !== null;

        return Offer::published()
            ->when($hasRadiusFilter, function (Builder $query) use ($lat, $lng): void {
                $query->selectRaw(
                    "offers.*, ({$this->haversineExpression()}) as distance_km",
                    $this->haversineBindings($lat, $lng),
                );
            })
            ->when($filters["search"] ?? null, function (Builder $query, string $search): void {
                $term = "%" . addcslashes(mb_strtolower($search), "%_\\") . "%";

                $query->where(function (Builder $q) use ($term): void {
                    $q->whereRaw("LOWER(title) LIKE ? ESCAPE '\\'", [$term])
                        ->orWhereRaw("LOWER(city) LIKE ? ESCAPE '\\'", [$term])
                        ->orWhereHas(
                            "company",
                            fn(Builder $q) => $q->whereRaw("LOWER(name) LIKE ? ESCAPE '\\'", [$term]),
                        );
                });
            })
            ->when(!$hasRadiusFilter && !empty($filters["cities"]), function (Builder $query) use ($filters): void {
                $query->whereIn("city", (array)$filters["cities"]);
            })
            ->when($filters["work_modes"] ?? null, function (Builder $query, array|string $workModes): void {
                $query->whereIn("work_mode", (array)$workModes);
            })
            ->when($filters["date_from"] ?? null, function (Builder $query, string $dateFrom): void {
                $query->where("start_date", ">=", $dateFrom);
            })
            ->when($filters["date_to"] ?? null, function (Builder $query, string $dateTo): void {
                $query->where("start_date", "<=", $dateTo);
            })
            ->when($filters["study_fields"] ?? null, function (Builder $query, array|string $studyFields): void {
                $query->whereHas("studyFields", function (Builder $q) use ($studyFields): void {
                    $q->whereIn("study_fields.id", (array)$studyFields);
                });
            })
            ->when($hasRadiusFilter, function (Builder $query) use ($lat, $lng, $radius): void {
                $query->whereNotNull("latitude")
                    ->whereNotNull("longitude")
                    ->whereRaw(
                        "({$this->haversineExpression()}) <= CAST(? AS DOUBLE PRECISION)",
                        [...$this->haversineBindings($lat, $lng), $radius],
                    );
            })
            ->when(
                $hasRadiusFilter,
                fn(Builder $query) => $query->orderBy("distance_km"),
                fn(Builder $query) => $query->orderBy("start_date"),
            );
    }

    protected function hasRadiusFilter(array $filters): bool
    {
        return isset($filters["latitude"], $filters["longitude"], $filters["radius_km"]);
    }

    protected function mapOfferToArray(
        Offer $offer,
        ?User $user,
        array $favoriteOfferIds,
        bool $hasRadiusFilter,
    ): array {
        $ownApplication = $user !== null
            ? $offer->applications->firstWhere("student_id", $user->id)
            : null;

        return [
            "id" => $offer->id,
            "title" => $offer->title,
            "city" => $offer->city,
            "work_mode" => $offer->work_mode?->value,
            "start_date" => $offer->start_date?->toDateString(),
            "end_date" => $offer->end_date?->toDateString(),
            "spots" => $offer->spots,
            "remaining_spots" => max(
                0,
                $offer->spots - $offer->applications->count(),
            ),
            "has_applied" => $ownApplication !== null,
            "applied_at" => $ownApplication?->created_at?->toDateString(),
            "is_favorite" => in_array($offer->id, $favoriteOfferIds, true),
            "study_field_ids" => $offer->studyFields->pluck("id")->all(),
            "company" => [
                "id" => $offer->company->id,
                "name" => $offer->company->name,
                "logo_path" => $offer->company->logo_path,
                "is_verified" => ($offer->company->verification_status ?? null)
                    === VerificationStatus::Verified,
            ],
            "longitude" => $offer->longitude,
            "latitude" => $offer->latitude,
            "distance_km" => $hasRadiusFilter
                ? round((float)$offer->distance_km, 1)
                : null,
        ];
    }

    private function haversineExpression(): string
    {
        return "6371 * acos(
        LEAST(1, GREATEST(-1,
            cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude))
        ))
    )";
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function haversineBindings(float $lat, float $lng): array
    {
        return [$lat, $lng, $lat];
    }
}
