<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("users", "email"),
                Rule::unique("users", "pending_email")->ignore($this->user()->id),
                $this->differentFromCurrentEmail(),
            ],
            "current_password" => ["required", "string", "current_password:web"],
        ];
    }

    public function messages(): array
    {
        return [
            "email.unique" => __("validation.email_taken"),
            "email.email" => __("validation.email_invalid_friendly"),
        ];
    }

    private function differentFromCurrentEmail(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === $this->user()->email) {
                $fail(__("validation.email_same_as_current"));
            }
        };
    }
}
