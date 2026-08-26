<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\OfferStatus;
use App\Models\Company;
use App\Models\Offer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetOffersSummary
{
    private const array ALLOWED_SORTS = ["title", "spots", "applications_count", "created_at"];

    public function execute(
        Company $company,
        string $sort = "created_at",
        string $direction = "desc",
        ?OfferStatus $status = null,
        ?string $search = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $direction = $direction === "asc" ? "asc" : "desc";

        if (!in_array($sort, self::ALLOWED_SORTS, true)) {
            $sort = "created_at";
        }

        $query = $company->offers()->withCount(["applications", "acceptedApplications"]);

        if ($status !== null) {
            $query->where("status", $status->value);
        }

        if ($search !== null && trim($search) !== "") {
            $term = "%" . addcslashes(mb_strtolower($search), "%_\\") . "%";
            $query->whereRaw("LOWER(title) LIKE ? ESCAPE '\\'", [$term]);
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderByDesc("id")
            ->paginate($perPage)
            ->through(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "status" => $offer->status->value,
                "spots" => $offer->spots,
                "remaining_spots" => $offer->remainingSpots(),
                "applications_count" => $offer->applications_count,
            ]);
    }
}
