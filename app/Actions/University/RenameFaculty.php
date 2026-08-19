<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Faculty;

class RenameFaculty
{
    public function execute(Faculty $faculty, string $name): void
    {
        $faculty->update([
            "name" => $name,
        ]);
    }
}
