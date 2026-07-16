<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Models\Offer;
use Illuminate\Support\Collection;

class GetOffersSummary
{
    /**
     * @return Collection<int, array{
     *     id: string,
     *     title: string,
     *     status: string,
     *     spots: int,
     *     applications_count: int,
     * }>
     */
    public function execute(Company $company): Collection
    {
        return $company->offers()
            ->withCount("applications")
            ->orderByDesc("created_at")
            ->get()
            ->map(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "status" => $offer->status->value,
                "spots" => $offer->spots,
                "applications_count" => $offer->applications_count,
            ]);
    }
}
