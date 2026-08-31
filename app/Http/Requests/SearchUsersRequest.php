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
            "sort_key" => ["nullable", "string", Rule::in(["name", "email", "role", "status", "created_at"])],
            "sort_dir" => ["nullable", "string", Rule::in(["asc", "desc"])],
        ];
    }

    public function getData(): array
    {
        $role = $this->validated("role");
        $search = $this->validated("search");
        $sortKey = $this->validated("sort_key");
        $sortDir = $this->validated("sort_dir");

        return [
            "role" => filled($role) ? $role : "all",
            "search" => filled($search) ? $search : "",
            "sort_key" => filled($sortKey) ? $sortKey : "created_at",
            "sort_dir" => filled($sortDir) ? $sortDir : "desc",
        ];
    }
}
