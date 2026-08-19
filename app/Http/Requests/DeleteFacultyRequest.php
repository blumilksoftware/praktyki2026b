<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteFacultyRequest extends FormRequest
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
            "reassign_to" => [
                Rule::requiredIf(fn(): bool => $this->isInUse($faculty)),
                "nullable",
                "uuid",
                "not_in:" . $faculty->id,
                Rule::exists("faculties", "id")->where("university_id", $faculty->university_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "reassign_to.required" => __("validation.faculty_reassign_required"),
        ];
    }

    public function attributes(): array
    {
        return [
            "reassign_to" => __("validation.attributes.faculty_reassign_to"),
        ];
    }

    private function isInUse(Faculty $faculty): bool
    {
        return $faculty->studyFields()
            ->where(
                fn(Builder $query): Builder => $query->whereHas("students")->orWhereHas("offers"),
            )
            ->exists();
    }
}
