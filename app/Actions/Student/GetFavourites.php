<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\Offer;
use App\Models\User;

class GetFavourites
{
    public function execute(User $student): array
    {
        return $student->favourites()
            ->with("company")
            ->withCount("acceptedApplications")
            ->orderByPivotDesc("created_at")
            ->get()
            ->map(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "company_name" => $offer->company->name,
                "city" => $offer->city,
                "remaining_spots" => $offer->remainingSpots(),
                "work_mode" => $offer->work_mode->value,
                "status" => $offer->status->value,
                "deleted_at" => $offer->deleted_at?->toIso8601String(),
                "saved_at" => $offer->pivot->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
