<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Offer;
use App\Models\User;
use App\Models\University;
use App\Models\StudyField;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetOfferDetailsAction
{
    public function execute(Offer $offer, ?User $user): array
    {
        if ($offer->status === OfferStatus::Draft) {
            throw new NotFoundHttpException();
        }

        $offer->loadMissing(["company", "studyFields", "universities", "applications"]);

        $isStudent = $user !== null && $user->role === UserRole::Student;
        $ownApplication = $isStudent
            ? $offer->applications->firstWhere("student_id", $user->id)
            : null;
        $favoriteOfferIds = $isStudent
            ? $user->favourites()->pluck("offers.id")->all()
            : [];

        return [
            "id" => $offer->id,
            "title" => $offer->title,
            "description" => $offer->description,
            "city" => $offer->city,
            "work_mode" => $offer->work_mode->value ?? null,
            "start_date" => $offer->start_date?->toDateString(),
            "end_date" => $offer->end_date?->toDateString(),
            "spots" => $offer->spots,
            "remaining_spots" => max(0, $offer->spots - $offer->applications->count()),
            "status" => $offer->status->value,
            "is_paid" => $offer->is_paid,
            "salary_min" => $offer->salary_min,
            "salary_max" => $offer->salary_max,
            "study_fields" => $offer->studyFields
                ->map(fn(StudyField $field): array => [
                    "id" => $field->id,
                    "name" => $field->name,
                ])
                ->values()
                ->all(),
            "preferred_universities" => $offer->universities
                ->map(fn(University $university): array => [
                    "id" => $university->id,
                    "name" => $university->name,
                ])
                ->values()
                ->all(),
            "has_applied" => $ownApplication !== null,
            "applied_at" => $ownApplication?->created_at?->toDateString(),
            "is_favorite" => in_array($offer->id, $favoriteOfferIds, true),
            "company" => [
                "id" => $offer->company->id,
                "name" => $offer->company->name,
                "logo_path" => $offer->company->logo_path,
                "is_verified" => ($offer->company->verification_status ?? null) === VerificationStatus::Verified,
            ],
        ];
    }
}
