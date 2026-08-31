<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Carbon\Carbon;
use Exception;
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
            "study_fields.*" => ["string", "uuid", "exists:study_fields,id"],
            "radius_city" => ["nullable", "string", "max:255"],
            "latitude" => ["nullable", "numeric", "between:-90,90", "required_with:radius_km"],
            "longitude" => ["nullable", "numeric", "between:-180,180", "required_with:radius_km"],
            "radius_km" => ["nullable", "integer", "in:10,25,50,100"],
        ];
    }

    protected function prepareForValidation(): void
    {
        $search = $this->input("search");
        $search = is_string($search) ? trim($search) : "";

        $dateFrom = $this->sanitizeDate($this->input("date_from"));
        $dateTo = $this->sanitizeDate($this->input("date_to"));

        if ($dateFrom !== null && $dateTo !== null && $dateTo < $dateFrom) {
            $dateFrom = null;
            $dateTo = null;
        }

        $this->merge([
            "search" => $search !== "" ? $search : null,
            "radius_km" => $this->filled("radius_km") ? (int)$this->input("radius_km") : null,
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
        ]);
    }

    private function sanitizeDate(mixed $value): ?string
    {
        if (!is_string($value) || $value === "") {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Exception) {
            return null;
        }
    }
}
