<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "status" => ["required", Rule::in([UserStatus::Active->value, UserStatus::Blocked->value])],
        ];
    }

    public function getStatus(): UserStatus
    {
        return UserStatus::from($this->validated("status"));
    }
}
