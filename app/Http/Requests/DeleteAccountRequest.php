<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "password" => ["required", "string", "current_password:web"],
            "confirmation" => ["required", "accepted"],
        ];
    }

    public function messages(): array
    {
        return [
            "confirmation.accepted" => __("validation.delete_confirmation_required"),
        ];
    }
}
