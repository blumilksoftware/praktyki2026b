<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchCompaniesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => ["nullable", "string", "max:255"],
            "city" => ["nullable", "string", "max:255"],
            "tag" => ["nullable", "string", "max:255"],
            "per_page" => ["nullable", "integer", Rule::in([15, 30, 50])],
        ];
    }

    public function getData(): array
    {
        $name = $this->validated("name");
        $city = $this->validated("city");
        $tag = $this->validated("tag");
        $perPage = $this->validated("per_page");

        return [
            "name" => filled($name) ? $name : null,
            "city" => filled($city) ? $city : null,
            "tag" => filled($tag) ? $tag : null,
            "per_page" => filled($perPage) ? $perPage : 15,
        ];
    }
}
