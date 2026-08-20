<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\StudyField;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudyFieldRequest extends FormRequest
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
            "name" => [
                "required",
                "string",
                "max:255",
                new UniqueNameRule(
                    StudyField::query()
                        ->where("faculty_id", $studyField->faculty_id)
                        ->whereKeyNot($studyField->id),
                    "validation.study_field_name_unique",
                ),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            "name" => __("validation.attributes.study_field_name"),
        ];
    }
}
