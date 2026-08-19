<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;

class CreateFaculty
{
    public function execute(University $university, string $name): void
    {
        $university->faculties()->create([
            "name" => $name,
        ]);
    }
}
