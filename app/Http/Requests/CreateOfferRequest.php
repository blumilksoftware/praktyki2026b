<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OfferStatus;
use App\Enums\WorkMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "title" => ["required", "string", "max:255"],
            "description" => ["required", "string", "max:10000"],
            "spots" => ["required", "integer", "min:1"],
            "city" => ["required", "string", "max:255"],
            "start_date" => ["required", "date"],
            "end_date" => ["required", "date", "after:start_date"],
            "work_mode" => ["required", Rule::enum(WorkMode::class)],
            "status" => ["required", Rule::enum(OfferStatus::class)],
            "is_paid" => ["required", "boolean"],
            "salary_min" => ["nullable", "required_if:is_paid,true", "integer", "min:0"],
            "salary_max" => ["nullable", "required_if:is_paid,true", "integer", "min:0", "gte:salary_min"],
            "study_field_ids" => ["sometimes", "array"],
            "study_field_ids.*" => ["uuid", "exists:study_fields,id"],
            "university_ids" => ["sometimes", "array"],
            "university_ids.*" => ["uuid", "exists:universities,id"],
        ];
    }

    public function getData(): array
    {
        $isPaid = $this->boolean("is_paid");

        return [
            "title" => $this->string("title")->toString(),
            "description" => $this->string("description")->toString(),
            "spots" => (int)$this->input("spots"),
            "city" => $this->string("city")->toString(),
            "start_date" => $this->string("start_date")->toString(),
            "end_date" => $this->string("end_date")->toString(),
            "work_mode" => WorkMode::from($this->string("work_mode")->toString()),
            "status" => OfferStatus::from($this->string("status")->toString()),
            "is_paid" => $isPaid,
            "salary_min" => $isPaid ? (int)$this->input("salary_min") : null,
            "salary_max" => $isPaid ? (int)$this->input("salary_max") : null,
            "study_field_ids" => $this->input("study_field_ids", []),
            "university_ids" => $this->input("university_ids", []),
        ];
    }
}
