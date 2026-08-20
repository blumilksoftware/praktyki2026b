<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use App\Traits\FiltersStudentOffers;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetStudentOffersAction
{
    use FiltersStudentOffers;

    public function execute(?User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $favoriteOfferIds = $user?->favourites()->pluck("offers.id")->all() ?? [];
        $hasRadiusFilter = $this->hasRadiusFilter($filters);

        return $this->buildFilteredOffersQuery($filters)
            ->with(["company", "applications", "studyFields"])
            ->withCount("acceptedApplications")
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($offer) => $this->mapOfferToArray($offer, $user, $favoriteOfferIds, $hasRadiusFilter));
    }
}
