<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Faculty;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $university = $this->user()?->universityOrganization;

        return [
            "name" => [
                "required",
                "string",
                "max:255",
                new UniqueNameRule(
                    Faculty::query()->where("university_id", $university?->id),
                    "validation.faculty_name_unique",
                ),
            ],
        ];
    }
}
