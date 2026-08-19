<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\VerificationStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetStudentOffersAction
{
    public function execute(?User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $favoriteOfferIds = $user?->favourites()->pluck("offers.id")->all() ?? [];

        $lat = isset($filters["latitude"]) ? (float)$filters["latitude"] : null;
        $lng = isset($filters["longitude"]) ? (float)$filters["longitude"] : null;
        $radius = isset($filters["radius_km"]) ? (float)$filters["radius_km"] : null;

        $hasRadiusFilter = $lat !== null && $lng !== null && $radius !== null;

        return Offer::published()
            ->with(["company", "applications", "studyFields"])

            ->when($hasRadiusFilter, function (Builder $query) use ($lat, $lng): void {
                $query->selectRaw(
                    "offers.*, ({$this->haversineExpression()}) as distance_km",
                    $this->haversineBindings($lat, $lng),
                );
            })

            ->when($filters["search"] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where("title", "like", "%{$search}%")
                        ->orWhere("city", "like", "%{$search}%")
                        ->orWhereHas(
                            "company",
                            fn(Builder $q) => $q->where("name", "like", "%{$search}%"),
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
                        "({$this->haversineExpression()}) <= CAST(? AS REAL)",
                        [...$this->haversineBindings($lat, $lng), $radius],
                    );
            })

            ->when(
                $hasRadiusFilter,
                fn(Builder $query) => $query->orderBy("distance_km"),
                fn(Builder $query) => $query->orderBy("start_date"),
            )

            ->paginate($perPage)
            ->withQueryString()

            ->through(function (Offer $offer) use (
                $user,
                $favoriteOfferIds,
                $hasRadiusFilter
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
            });
    }

    private function haversineExpression(): string
    {
        return "6371 * acos(
            cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude))
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
