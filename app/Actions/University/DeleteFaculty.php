<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Faculty;
use App\Models\StudyField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeleteFaculty
{
    public function __construct(
        private readonly DeleteStudyField $deleteStudyField,
    ) {}

    public function execute(Faculty $faculty, ?Faculty $reassignTo = null): void
    {
        DB::transaction(function () use ($faculty, $reassignTo): void {
            if ($reassignTo !== null) {
                $fieldsByName = $reassignTo->studyFields()
                    ->get()
                    ->keyBy(static fn(StudyField $studyField): string => Str::lower($studyField->name));

                foreach ($faculty->studyFields()->get() as $studyField) {
                    $duplicate = $fieldsByName->get(Str::lower($studyField->name));

                    if ($duplicate instanceof StudyField) {
                        $this->deleteStudyField->execute($studyField, $duplicate);

                        continue;
                    }

                    $studyField->update(["faculty_id" => $reassignTo->id]);
                }
            }

            $faculty->delete();
        });
    }
}
