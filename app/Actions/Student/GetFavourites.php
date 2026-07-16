<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\Offer;
use App\Models\User;

class GetFavourites
{
    /**
     * @return list<array{
     *     id: string,
     *     title: string,
     *     company_name: string,
     *     city: string,
     *     work_mode: string,
     *     status: string,
     *     deleted_at: ?string,
     *     saved_at: string,
     * }>
     */
    public function execute(User $student): array
    {
        return $student->favourites()
            ->with("company")
            ->orderByPivotDesc("created_at")
            ->get()
            ->map(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "company_name" => $offer->company->name,
                "city" => $offer->city,
                "work_mode" => $offer->work_mode->value,
                "status" => $offer->status->value,
                "deleted_at" => $offer->deleted_at?->toIso8601String(),
                "saved_at" => $offer->pivot->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
