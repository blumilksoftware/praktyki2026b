<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Traits\FiltersStudentOffers;
use Illuminate\Support\Collection;

class GetStudentOffersMapCitiesAction
{
    use FiltersStudentOffers;

    public function execute(array $filters = []): Collection
    {
        return $this->buildFilteredOffersQuery($filters)
            ->whereNotNull("offers.latitude")
            ->whereNotNull("offers.longitude")
            ->reorder()
            ->select("offers.city")
            ->selectRaw("COUNT(*) as offers_count")
            ->selectRaw("AVG(offers.latitude) as latitude")
            ->selectRaw("AVG(offers.longitude) as longitude")
            ->groupBy("offers.city")
            ->get()
            ->map(fn($row) => [
                "city" => $row->city,
                "offers_count" => (int)$row->offers_count,
                "latitude" => (float)$row->latitude,
                "longitude" => (float)$row->longitude,
            ])
            ->values();
    }
}
