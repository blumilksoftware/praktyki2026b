<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["required", "string", "max:255"],
            "password" => ["required", "confirmed", Password::defaults()],
            "terms" => ["required", "accepted"],
        ];
    }

    public function messages(): array
    {
        return [
            "password.confirmed" => __("validation.password_confirmed_friendly"),
            "terms.accepted" => __("validation.terms_accepted_friendly"),
        ];
    }

    public function getData(): array
    {
        return [
            "first_name" => $this->string("first_name")->toString(),
            "last_name" => $this->string("last_name")->toString(),
            "password" => $this->string("password")->toString(),
        ];
    }
}
