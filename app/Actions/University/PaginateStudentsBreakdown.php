<?php

declare(strict_types=1);

namespace App\Actions\University;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PaginateStudentsBreakdown
{
    public function execute(
        Collection $rows,
        string $nameColumn,
        int $perPage,
        int $page,
        string $pageName,
        ?string $search,
        string $sortBy,
        string $sortDirection,
    ): LengthAwarePaginatorContract {
        if ($search !== null && $search !== "") {
            $needle = Str::lower($search);
            $rows = $rows->filter(
                static fn(array $row): bool => $row[$nameColumn] !== null
                    && str_contains(Str::lower($row[$nameColumn]), $needle),
            )->values();
        }

        $sortableColumns = [$nameColumn, "linkedStudents", "applicationsSubmitted", "acceptedPlacements"];
        $sortBy = in_array($sortBy, $sortableColumns, true) ? $sortBy : $nameColumn;

        $rows = $rows
            ->sortBy(
                static fn(array $row): int|string|null => $row[$sortBy],
                SORT_REGULAR,
                strtolower($sortDirection) === "desc",
            )
            ->values();

        return new LengthAwarePaginator(
            items: $rows->slice(($page - 1) * $perPage, $perPage)->values(),
            total: $rows->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                "path" => LengthAwarePaginator::resolveCurrentPath(),
                "pageName" => $pageName,
            ],
        );
    }
}
