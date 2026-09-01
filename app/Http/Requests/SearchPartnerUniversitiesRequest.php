<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PartnershipStatusFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPartnerUniversitiesRequest extends FormRequest
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
            "status" => ["nullable", "string", Rule::enum(PartnershipStatusFilter::class)],
            "per_page" => ["nullable", "integer", Rule::in([15, 30, 50])],
        ];
    }

    public function getData(): array
    {
        $name = $this->validated("name");
        $city = $this->validated("city");
        $partnershipStatus = $this->validated("status");
        $perPage = $this->validated("per_page");

        return [
            "name" => filled($name) ? $name : null,
            "city" => filled($city) ? $city : null,
            "status" => filled($partnershipStatus) ? $partnershipStatus : null,
            "per_page" => filled($perPage) ? $perPage : 15,
        ];
    }
}
