<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use App\Traits\FiltersStudentOffers;
use Illuminate\Support\Collection;

class GetStudentOffersForMapAction
{
    use FiltersStudentOffers;

    public function execute(?User $user, array $filters = []): Collection
    {
        $favoriteOfferIds = $user?->favourites()->pluck("offers.id")->all() ?? [];
        $hasRadiusFilter = $this->hasRadiusFilter($filters);

        return $this->buildFilteredOffersQuery($filters)
            ->with(["company", "applications", "studyFields"])
            ->withCount("acceptedApplications")
            ->whereNotNull("latitude")
            ->whereNotNull("longitude")
            ->get()
            ->map(fn($offer) => $this->mapOfferToArray($offer, $user, $favoriteOfferIds, $hasRadiusFilter))
            ->values();
    }
}
