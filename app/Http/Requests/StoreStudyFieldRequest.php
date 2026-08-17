<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Faculty;
use App\Models\StudyField;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudyFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Faculty $faculty */
        $faculty = $this->route("faculty");

        return [
            "name" => [
                "required",
                "string",
                "max:255",
                new UniqueNameRule(
                    StudyField::query()->where("faculty_id", $faculty->id),
                    "validation.study_field_name_unique",
                ),
            ],
        ];
    }
}
