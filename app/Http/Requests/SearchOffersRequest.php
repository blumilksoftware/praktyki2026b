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
        ];
    }

    public function getData(): array
    {
        $status = $this->validated("status");
        $search = $this->validated("search");

        return [
            "status" => filled($status) ? $status : "all",
            "search" => filled($search) ? $search : "",
        ];
    }
}
