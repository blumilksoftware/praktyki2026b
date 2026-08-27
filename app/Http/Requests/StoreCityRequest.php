<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\City;
use App\Rules\UniqueNameRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
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
                new UniqueNameRule(City::query(), "validation.city_name_unique"),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            "name" => __("validation.attributes.city_name"),
        ];
    }
}
