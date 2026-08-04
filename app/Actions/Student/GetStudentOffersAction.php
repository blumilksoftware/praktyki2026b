<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\VerificationStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Collection;

class GetStudentOffersAction
{
    public function execute(?User $user): Collection
    {
        $favoriteOfferIds = $user?->favourites()->pluck("offers.id")->all() ?? [];

        return Offer::published()
            ->with(["company", "applications", "studyFields"])
            ->get()
            ->map(function (Offer $offer) use ($user, $favoriteOfferIds): array {
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
                    "remaining_spots" => max(0, $offer->spots - $offer->applications->count()),
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
                ];
            });
    }
}
