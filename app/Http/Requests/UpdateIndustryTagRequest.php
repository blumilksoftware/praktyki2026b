<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\IndustryTag;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIndustryTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var IndustryTag $industryTag */
        $industryTag = $this->route("industryTag");

        return [
            "name" => [
                "required",
                "string",
                "max:255",
                new UniqueNameRule(IndustryTag::query()->whereKeyNot($industryTag->id), "validation.industry_tag_name_unique"),
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
