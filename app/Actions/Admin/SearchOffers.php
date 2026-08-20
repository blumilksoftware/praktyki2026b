<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Offer;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchOffers
{
    public function execute(string $status, string $search): LengthAwarePaginator
    {
        $query = Offer::query()->with("company:id,name");

        if ($status !== "all") {
            $query->where("status", $status);
        }

        if ($search !== "") {
            $query->where(function ($builder) use ($search): void {
                $builder->where("title", "like", "%{$search}%")
                    ->orWhereHas("company", function ($company) use ($search): void {
                        $company->where("name", "like", "%{$search}%");
                    });
            });
        }

        return $query->orderBy("created_at", "desc")->paginate(20)->appends([
            "status" => $status,
            "search" => $search,
        ]);
    }
}
