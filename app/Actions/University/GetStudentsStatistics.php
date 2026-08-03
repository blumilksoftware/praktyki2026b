<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;
use Illuminate\Support\Carbon;

class GetStudentsStatistics
{
    public function __construct(
        private readonly GetLinkedStudents $getLinkedStudents,
        private readonly GetStudentsBreakdownByFaculty $getBreakdownByFaculty,
        private readonly GetStudentsBreakdownByField $getBreakdownByField,
    ) {}

    public function execute(
        University $university,
        ?Carbon $from = null,
        ?Carbon $to = null,
        int $fieldPage = 1,
        int $fieldPerPage = 10,
        ?string $fieldSearch = null,
        string $fieldSortBy = "fieldName",
        string $fieldSortDirection = "asc",
        int $facultyPage = 1,
        int $facultyPerPage = 10,
        ?string $facultySearch = null,
        string $facultySortBy = "facultyName",
        string $facultySortDirection = "asc",
    ): array {
        $linkedStudents = $this->getLinkedStudents->execute($university, $from, $to);

        return [
            "linkedStudents" => $linkedStudents->count(),
            "applicationsSubmitted" => $linkedStudents->sum("applications_submitted_count"),
            "acceptedPlacements" => $linkedStudents->sum("accepted_placements_count"),
            "breakdownByFaculty" => $this->getBreakdownByFaculty->execute(
                students: $linkedStudents,
                perPage: $facultyPerPage,
                page: $facultyPage,
                pageName: "facultyPage",
                search: $facultySearch,
                sortBy: $facultySortBy,
                sortDirection: $facultySortDirection,
            ),
            "breakdownByField" => $this->getBreakdownByField->execute(
                students: $linkedStudents,
                perPage: $fieldPerPage,
                page: $fieldPage,
                pageName: "fieldPage",
                search: $fieldSearch,
                sortBy: $fieldSortBy,
                sortDirection: $fieldSortDirection,
            ),
        ];
    }
}
