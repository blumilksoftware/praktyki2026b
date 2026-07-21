<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\BuildingNumberRule;
use App\Rules\PostalCodeRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["required", "string", "max:255"],
            "age" => ["nullable", "integer", "min:0", "max:255"],
            "street" => ["nullable", "string", "max:255"],
            "building_number" => ["nullable", "string", "max:10", new BuildingNumberRule()],
            "postal_code" => ["nullable", "string", new PostalCodeRule()],
            "city" => ["nullable", "string", "max:255"],
            "university" => ["nullable", "string", "max:255"],
            "study_field" => ["nullable", "string", "max:255"],
            "study_year" => ["nullable", "integer", "min:0", "max:255"],
            "specialization" => ["nullable", "string", "max:255"],
            "study_field_ids" => ["nullable", "array"],
            "study_field_ids.*" => ["string", "uuid", "exists:study_fields,id"],
            "preferred_cities" => ["nullable", "array", "max:10"],
            "preferred_cities.*" => ["required", "string", "max:255"],
        ];
    }

    public function messages(): array
    {
        return [
            "age.integer" => __("validation.profile_age_integer"),
            "age.min" => __("validation.profile_age_min"),
            "age.max" => __("validation.profile_age_max"),
            "study_year.integer" => __("validation.profile_study_year_integer"),
            "study_year.min" => __("validation.profile_study_year_min"),
            "study_year.max" => __("validation.profile_study_year_max"),
            "preferred_cities.max" => __("validation.profile_preferred_cities_max"),
        ];
    }

    public function getData(): array
    {
        return [
            "first_name" => $this->string("first_name")->toString(),
            "last_name" => $this->string("last_name")->toString(),
            "age" => $this->filled("age") ? $this->integer("age") : null,
            "street" => $this->input("street"),
            "building_number" => $this->input("building_number"),
            "postal_code" => $this->input("postal_code"),
            "city" => $this->input("city"),
            "university" => $this->input("university"),
            "study_field" => $this->input("study_field"),
            "study_year" => $this->filled("study_year") ? $this->integer("study_year") : null,
            "specialization" => $this->input("specialization"),
            "study_field_ids" => $this->input("study_field_ids", []),
            "preferred_cities" => $this->input("preferred_cities", []),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled("postal_code")) {
            $this->merge([
                "postal_code" => $this->normalizePostalCode((string)$this->input("postal_code")),
            ]);
        }
    }

    private function normalizePostalCode(string $postalCode): string
    {
        $clean = preg_replace("/[^0-9]/", "", $postalCode);

        if (strlen($clean) === 5) {
            return substr($clean, 0, 2) . "-" . substr($clean, 2);
        }

        return $postalCode;
    }
}
