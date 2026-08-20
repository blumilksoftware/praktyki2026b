<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Faculty;
use App\Models\StudyField;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteStudyFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var StudyField $studyField */
        $studyField = $this->route("studyField");

        return [
            "reassign_to" => [
                Rule::requiredIf(fn(): bool => $this->isInUse($studyField)),
                "nullable",
                "uuid",
                "not_in:" . $studyField->id,
                Rule::exists("study_fields", "id")->where(
                    fn(Builder $query): Builder => $query->whereIn(
                        "faculty_id",
                        Faculty::query()
                            ->where("university_id", $studyField->faculty->university_id)
                            ->select("id"),
                    ),
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "reassign_to.required" => __("validation.study_field_reassign_required"),
        ];
    }

    public function attributes(): array
    {
        return [
            "reassign_to" => __("validation.attributes.study_field_reassign_to"),
        ];
    }

    private function isInUse(StudyField $studyField): bool
    {
        return $studyField->students()->exists() || $studyField->offers()->exists();
    }
}
