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

        return Offer::published()
            ->with(["company", "applications", "studyFields"])
            ->withCount("acceptedApplications")
            ->when($filters["search"] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where("title", "ilike", "%{$search}%")
                        ->orWhere("city", "ilike", "%{$search}%")
                        ->orWhereHas("company", fn(Builder $q) => $q->where("name", "ilike", "%{$search}%"));
                });
            })
            ->when($filters["cities"] ?? null, function (Builder $query, array|string $cities): void {
                $query->whereIn("city", (array)$cities);
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
            ->orderBy("start_date")
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Offer $offer) use ($user, $favoriteOfferIds): array {
                $ownApplication = $user !== null
                    ? $offer->applications->firstWhere("student_id", $user->id)
                    : null;

                return [
                    "id" => $offer->id,
                    "title" => $offer->title,
                    "city" => $offer->city,
                    "work_mode" => $offer->work_mode->value ?? null,
                    "start_date" => $offer->start_date?->toDateString(),
                    "end_date" => $offer->end_date?->toDateString(),
                    "spots" => $offer->spots,
                    "remaining_spots" => $offer->remainingSpots(),
                    "has_applied" => $ownApplication !== null,
                    "applied_at" => $ownApplication?->created_at?->toDateString(),
                    "is_favorite" => in_array($offer->id, $favoriteOfferIds, true),
                    "study_field_ids" => $offer->studyFields->pluck("id")->all(),
                    "company" => [
                        "id" => $offer->company->id,
                        "name" => $offer->company->name,
                        "logo_path" => $offer->company->logo_path,
                        "is_verified" => ($offer->company->verification_status ?? null) === VerificationStatus::Verified,
                    ],
                    "longitude" => $offer->longitude,
                    "latitude" => $offer->latitude,
                ];
            });
    }
}
