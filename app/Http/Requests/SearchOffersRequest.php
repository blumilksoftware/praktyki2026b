<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\WorkMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchOffersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "study_field_ids" => ["sometimes", "array", "distinct"],
            "study_field_ids.*" => ["uuid", "exists:study_fields,id"],
            "work_mode" => ["sometimes", Rule::enum(WorkMode::class)],
            "city" => ["sometimes", "string", "max:255"],
            "date_from" => ["sometimes", "date"],
            "date_to" => ["sometimes", "date", "after_or_equal:date_from"],
            "date_flex_days" => ["sometimes", "integer", "min:0", "max:365"],
            "per_page" => ["sometimes", Rule::in([15, 30, 50])],
        ];
    }

    public function getData(): array
    {
        $workMode = $this->validated("work_mode");
        $city = $this->validated("city");
        $dateFrom = $this->validated("date_from");
        $dateTo = $this->validated("date_to");
        $dateFlexDays = $this->validated("date_flex_days");
        $perPage = $this->validated("per_page");

        return [
            "study_field_ids" => $this->validated("study_field_ids", []),
            "work_mode" => filled($workMode) ? WorkMode::from((string)$workMode) : null,
            "city" => filled($city) ? (string)$city : null,
            "date_from" => filled($dateFrom) ? (string)$dateFrom : null,
            "date_to" => filled($dateTo) ? (string)$dateTo : null,
            "date_flex_days" => filled($dateFlexDays) ? (int)$dateFlexDays : 0,
            "per_page" => filled($perPage) ? (int)$perPage : 15,
        ];
    }
}
