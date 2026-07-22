<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\University;
use App\Models\User;

class LinkStudentToUniversity
{
    public function execute(User $student, string $universityId): User
    {
        $university = University::findOrFail($universityId);

        $student->update([
            "organization_id" => $university->id,
            "university" => $university->name,
        ]);

        return $student->fresh();
    }
}
