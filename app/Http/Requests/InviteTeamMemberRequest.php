<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->role === UserRole::CompanyAdmin
            && $user->status === UserStatus::Active;
    }

    public function rules(): array
    {
        return [
            "email" => ["required", "string", "email", "max:255", Rule::unique("users", "email")],
        ];
    }

    public function messages(): array
    {
        return [
            "email.unique" => __("validation.email_taken"),
            "email.email" => __("validation.email_invalid_friendly"),
        ];
    }

    public function getData(): array
    {
        return [
            "email" => $this->string("email")->toString(),
        ];
    }
}
