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
            "study_field_ids" => ["sometimes", "array"],
            "study_field_ids.*" => ["uuid", "exists:study_fields,id"],
            "work_mode" => ["sometimes", Rule::enum(WorkMode::class)],
            "city" => ["sometimes", "string", "max:255"],
            "date_from" => ["nullable", "date"],
            "date_to" => ["nullable", "date", "after_or_equal:date_from"],
            "date_flex_days" => ["nullable", "integer", "min:0", "max:365"],
        ];
    }

    public function getData(): array
    {
        return [
            "study_field_ids" => $this->input("study_field_ids", []),
            "work_mode" => $this->filled("work_mode")
                ? WorkMode::from($this->string("work_mode")->toString())
                : null,
            "city" => $this->filled("city") ? $this->string("city")->toString() : null,
            "date_from" => $this->filled("date_from") ? $this->string("date_from")->toString() : null,
            "date_to" => $this->filled("date_to") ? $this->string("date_to")->toString() : null,
            "date_flex_days" => $this->filled("date_flex_days") ? (int)$this->input("date_flex_days") : 0,
        ];
    }
}
