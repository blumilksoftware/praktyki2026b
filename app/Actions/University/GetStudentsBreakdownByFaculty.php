<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetStudentsBreakdownByFaculty
{
    private const array SORTABLE_COLUMNS = [
        "facultyName",
        "linkedStudents",
        "applicationsSubmitted",
        "acceptedPlacements",
    ];

    public function execute(
        Collection $students,
        int $perPage = 10,
        int $page = 1,
        string $pageName = "facultyPage",
        ?string $search = null,
        string $sortBy = "facultyName",
        string $sortDirection = "asc",
    ): LengthAwarePaginatorContract {
        $grouped = $students
            ->groupBy(static fn(User $student): string => $student->studyField?->faculty_id ?? "unknown")
            ->map(static fn(Collection $group): array => [
                "facultyId" => $group->first()->studyField?->faculty_id,
                "facultyName" => $group->first()->studyField?->faculty?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => $group->sum("applications_submitted_count"),
                "acceptedPlacements" => $group->sum("accepted_placements_count"),
            ])
            ->values();

        if ($search !== null && $search !== "") {
            $needle = Str::lower($search);
            $grouped = $grouped->filter(
                static fn(array $row): bool => $row["facultyName"] !== null
                    && str_contains(Str::lower($row["facultyName"]), $needle),
            )->values();
        }

        $sortBy = in_array($sortBy, self::SORTABLE_COLUMNS, true) ? $sortBy : "facultyName";
        $descending = strtolower($sortDirection) === "desc";

        $grouped = $grouped
            ->sortBy(
                static fn(array $row): int|string|null => $row[$sortBy],
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
