<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "university" => [
                "required",
                "string",
                "uuid",
                Rule::exists("universities", "id")->where("verification_status", VerificationStatus::Verified),
            ],
        ];
    }
}
