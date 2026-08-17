<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Support\Collection;

class GetStudentsBreakdownByFaculty
{
    public function __construct(
        private readonly PaginateStudentsBreakdown $paginateStudentsBreakdown,
    ) {}

    public function execute(
        Collection $students,
        int $perPage = 10,
        int $page = 1,
        string $pageName = "facultyPage",
        ?string $search = null,
        string $sortBy = "facultyName",
        string $sortDirection = "asc",
    ): LengthAwarePaginatorContract {
        $rows = $students
            ->groupBy(static fn(User $student): string => $student->studyField?->faculty_id ?? "unknown")
            ->map(static fn(Collection $group): array => [
                "facultyId" => $group->first()->studyField?->faculty_id,
                "facultyName" => $group->first()->studyField?->faculty?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => $group->sum("applications_submitted_count"),
                "acceptedPlacements" => $group->sum("accepted_placements_count"),
            ])
            ->values();

        return $this->paginateStudentsBreakdown->execute(
            rows: $rows,
            nameColumn: "facultyName",
            perPage: $perPage,
            page: $page,
            pageName: $pageName,
            search: $search,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
        );
    }
}
