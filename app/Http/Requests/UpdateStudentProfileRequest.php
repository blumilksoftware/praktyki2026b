<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            "location" => ["nullable", "string", "max:255"],
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
            "age" => $this->input("age"),
            "location" => $this->input("location"),
            "university" => $this->input("university"),
            "study_field" => $this->input("study_field"),
            "study_year" => $this->input("study_year"),
            "specialization" => $this->input("specialization"),
            "study_field_ids" => $this->input("study_field_ids", []),
            "preferred_cities" => $this->input("preferred_cities", []),
        ];
    }
}
