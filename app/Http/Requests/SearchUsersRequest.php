<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "role" => ["nullable", "string", Rule::in([...array_column(UserRole::cases(), "value"), "all"])],
            "search" => ["nullable", "string", "max:255"],
        ];
    }

    public function getData(): array
    {
        $role = $this->validated("role");
        $search = $this->validated("search");

        return [
            "role" => filled($role) ? $role : "all",
            "search" => filled($search) ? $search : "",
        ];
    }
}
