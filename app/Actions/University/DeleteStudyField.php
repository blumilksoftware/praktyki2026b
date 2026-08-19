<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\StudyField;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DeleteStudyField
{
    public function execute(StudyField $studyField, ?StudyField $reassignTo = null): void
    {
        DB::transaction(function () use ($studyField, $reassignTo): void {
            if ($reassignTo !== null) {
                User::query()
                    ->where("study_field_id", $studyField->id)
                    ->update(["study_field_id" => $reassignTo->id]);

                $this->movePivotRows("offer_study_field", "offer_id", $studyField, $reassignTo);
                $this->movePivotRows("student_study_field", "student_id", $studyField, $reassignTo);
            }

            $studyField->delete();
        });
    }

    private function movePivotRows(string $table, string $ownerColumn, StudyField $studyField, StudyField $reassignTo): void
    {
        DB::table($table)
            ->where("study_field_id", $studyField->id)
            ->whereIn(
                $ownerColumn,
                fn(Builder $query): Builder => $query
                    ->select($ownerColumn)
                    ->from($table)
                    ->where("study_field_id", $reassignTo->id),
            )
            ->delete();

        DB::table($table)
            ->where("study_field_id", $studyField->id)
            ->update(["study_field_id" => $reassignTo->id]);
    }
}
