<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Faculty;

class CreateStudyField
{
    public function execute(Faculty $faculty, string $name): void
    {
        $faculty->studyFields()->create([
            "name" => $name,
        ]);
    }
}
