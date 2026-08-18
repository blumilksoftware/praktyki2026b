<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Faculty;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFacultyRequest extends FormRequest
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
                    Faculty::query()
                        ->where("university_id", $faculty->university_id)
                        ->whereKeyNot($faculty->id),
                    "validation.faculty_name_unique",
                ),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            "name" => __("validation.attributes.faculty_name"),
        ];
    }
}
