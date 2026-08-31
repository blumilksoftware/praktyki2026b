<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Actions\Company\GetOffersSummary;
use App\Enums\OfferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyOfferSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "sort" => ["nullable", "string", Rule::in(GetOffersSummary::ALLOWED_SORTS)],
            "direction" => ["nullable", "string", Rule::in(["asc", "desc"])],
            "status" => ["nullable", "string", Rule::enum(OfferStatus::class)],
            "search" => ["nullable", "string", "max:255"],
            "closing_soon" => ["nullable", "boolean"],
            "per_page" => ["nullable", "integer", "min:1", "max:100"],
        ];
    }

    public function getData(): array
    {
        return [
            "sort" => $this->string("sort", "created_at")->toString(),
            "direction" => $this->string("direction", "desc")->toString(),
            "status" => OfferStatus::tryFrom($this->string("status")->toString()),
            "search" => $this->string("search")->toString() ?: null,
            "closing_soon" => $this->boolean("closing_soon", false),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            "search" => $this->filled("search") ? trim((string)$this->input("search")) : null,
            "closing_soon" => $this->boolean("closing_soon"),
        ]);
    }
}
