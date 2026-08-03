<?php

declare(strict_types=1);

namespace App\Actions\University;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetStudentsBreakdownByField
{
    private const array SORTABLE_COLUMNS = [
        "fieldName",
        "linkedStudents",
        "applicationsSubmitted",
        "acceptedPlacements",
    ];

    public function execute(
        Collection $students,
        int $perPage = 10,
        int $page = 1,
        string $pageName = "fieldPage",
        ?string $search = null,
        string $sortBy = "fieldName",
        string $sortDirection = "asc",
    ): LengthAwarePaginatorContract {
        $grouped = $students
            ->groupBy("study_field")
            ->map(static fn(Collection $group) => [
                "fieldId" => $group->first()->study_field,
                "fieldName" => $group->first()->studyField?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => (int)$group->sum("applications_submitted_count"),
                "acceptedPlacements" => (int)$group->sum("accepted_placements_count"),
            ])
            ->values();

        if ($search !== null && $search !== "") {
            $needle = Str::lower($search);
            $grouped = $grouped->filter(
                static fn(array $row) => $row["fieldName"] !== null
                    && str_contains(Str::lower($row["fieldName"]), $needle),
            )->values();
        }

        $sortBy = in_array($sortBy, self::SORTABLE_COLUMNS, true) ? $sortBy : "fieldName";
        $descending = strtolower($sortDirection) === "desc";

        $grouped = $grouped
            ->sortBy(
                static fn(array $row) => $row[$sortBy],
                SORT_REGULAR,
                $descending,
            )
            ->values();

        $total = $grouped->count();
        $slice = $grouped->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            items: $slice,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                "path" => LengthAwarePaginator::resolveCurrentPath(),
                "pageName" => $pageName,
            ],
        );
    }
}
