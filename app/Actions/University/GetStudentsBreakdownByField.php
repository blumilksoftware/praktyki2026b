<?php

declare(strict_types=1);

namespace App\Actions\University;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Support\Collection;

class GetStudentsBreakdownByField
{
    public function __construct(
        private readonly PaginateStudentsBreakdown $paginateStudentsBreakdown,
    ) {}

    public function execute(
        Collection $students,
        int $perPage = 10,
        int $page = 1,
        string $pageName = "fieldPage",
        ?string $search = null,
        string $sortBy = "fieldName",
        string $sortDirection = "asc",
    ): LengthAwarePaginatorContract {
        $rows = $students
            ->groupBy("study_field_id")
            ->map(static fn(Collection $group): array => [
                "fieldId" => $group->first()->study_field_id,
                "fieldName" => $group->first()->studyField?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => $group->sum("applications_submitted_count"),
                "acceptedPlacements" => $group->sum("accepted_placements_count"),
            ])
            ->values();

        return $this->paginateStudentsBreakdown->execute(
            rows: $rows,
            nameColumn: "fieldName",
            perPage: $perPage,
            page: $page,
            pageName: $pageName,
            search: $search,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
        );
    }
}
