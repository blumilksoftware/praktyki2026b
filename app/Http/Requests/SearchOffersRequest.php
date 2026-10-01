<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OfferStatus;
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
            "status" => ["nullable", "string", Rule::in([...array_column(OfferStatus::cases(), "value"), "all"])],
            "search" => ["nullable", "string", "max:255"],
            "company" => ["nullable", "bail", "uuid", Rule::exists("companies", "id")],
            "sort_key" => ["nullable", "string", Rule::in(["title", "company", "city", "status", "created_at"])],
            "sort_dir" => ["nullable", "string", Rule::in(["asc", "desc"])],
        ];
    }

    public function getData(): array
    {
        $status = $this->validated("status");
        $search = $this->validated("search");
        $company = $this->validated("company");
        $sortKey = $this->validated("sort_key");
        $sortDir = $this->validated("sort_dir");

        return [
            "status" => filled($status) ? $status : "all",
            "search" => filled($search) ? $search : "",
            "company" => filled($company) ? $company : "",
            "sort_key" => filled($sortKey) ? $sortKey : "created_at",
            "sort_dir" => filled($sortDir) ? $sortDir : "desc",
        ];
    }
}
