<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "search" => ["nullable", "string", "max:255"],
            "cities" => ["nullable", "array"],
            "cities.*" => ["string", "max:255"],
            "work_modes" => ["nullable", "array"],
            "work_modes.*" => ["string"],
            "date_from" => ["nullable", "date"],
            "date_to" => ["nullable", "date", "after_or_equal:date_from"],
            "study_fields" => ["nullable", "array"],
            "study_fields.*" => ["integer", "exists:study_fields,id"],
            "radius_city" => ["nullable", "string", "max:255"],
            "latitude" => ["nullable", "numeric", "between:-90,90", "required_with:radius_km"],
            "longitude" => ["nullable", "numeric", "between:-180,180", "required_with:radius_km"],
            "radius_km" => ["nullable", "integer", "in:10,25,50,100"],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            "search" => $this->filled("search") ? trim((string)$this->input("search")) : null,
            "radius_km" => $this->filled("radius_km") ? (int)$this->input("radius_km") : null,
        ]);
    }
}
