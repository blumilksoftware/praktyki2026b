<?php
declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Models\Offer;
use App\Enums\OfferStatus;
use Illuminate\Support\Collection;

class GetOffersSummary
{
    public function execute(Company $company, string $sort = "created_at", string $direction = "desc"): Collection
    {
        $allowedSorts = ["title", "status", "spots", "applications_count", "created_at"];
        $direction = $direction === "asc" ? "asc" : "desc";

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = "created_at";
        }

        $query = $company->offers()->withCount("applications");

        if ($sort === "applications_count") {
            $query->orderBy("applications_count", $direction);
        } elseif ($sort === "status") {
            [$sql, $bindings] = $this->statusOrderSql();
            $query->orderByRaw("{$sql} {$direction}", $bindings);
        } else {
            $query->orderBy($sort, $direction);
        }

        return $query
            ->orderByDesc("id")
            ->get()
            ->map(fn (Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "status" => $offer->status->value,
                "spots" => $offer->spots,
                "applications_count" => $offer->applications_count,
            ]);
    }

    private function statusOrderSql(): array
    {
        $cases = [];
        $bindings = [];
        foreach (OfferStatus::sortOrder() as $position => $status) {
            $cases[] = "WHEN ? THEN ?";
            $bindings[] = $status->value;
            $bindings[] = $position;
        }

        $sql = "CASE status " . implode(" ", $cases) . " ELSE ? END";
        $bindings[] = count(OfferStatus::sortOrder());

        return [$sql, $bindings];
    }
}
