<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\StudyField;

class RenameStudyField
{
    public function execute(StudyField $studyField, string $name): void
    {
        $studyField->update([
            "name" => $name,
        ]);
    }
}
