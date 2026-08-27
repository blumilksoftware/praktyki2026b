<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\IndustryTag;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIndustryTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => [
                "required",
                "string",
                "max:255",
                new UniqueNameRule(IndustryTag::query(), "validation.industry_tag_name_unique"),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            "name" => __("validation.attributes.industry_tag_name"),
        ];
    }
}
