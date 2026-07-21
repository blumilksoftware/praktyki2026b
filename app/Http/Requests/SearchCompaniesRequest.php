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
            "name" => ["sometimes", "nullable", "string", "max:255"],
            "city" => ["sometimes", "nullable", "string", "max:255"],
            "tag" => ["sometimes", "nullable", "string", "max:255"],
            "per_page" => ["sometimes", "nullable", "integer", Rule::in([15, 30, 50])],
        ];
    }

    public function getData(): array
    {
        $name = $this->validated("name");
        $city = $this->validated("city");
        $tag = $this->validated("tag");
        $perPage = $this->validated("per_page");

        return [
            "name" => filled($name) ? (string)$name : null,
            "city" => filled($city) ? (string)$city : null,
            "tag" => filled($tag) ? (string)$tag : null,
            "per_page" => filled($perPage) ? (int)$perPage : 15,
        ];
    }
}
