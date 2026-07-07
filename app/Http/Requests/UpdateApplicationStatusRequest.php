<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "status" => [
                "required",
                Rule::enum(ApplicationStatus::class)->except(ApplicationStatus::Pending),
            ],
        ];
    }
}
