<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "token" => ["required", "string"],
            "email" => ["required", "string", "email"],
            "password" => ["required", "string", Password::default(), "confirmed"],
        ];
    }
}
