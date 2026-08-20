<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OrganizationType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = UserRole::tryFrom((string)$this->input("role"));

        return [
            "role" => ["required", Rule::enum(UserRole::class)],
            "organization_id" => match ($role?->organizationType()) {
                OrganizationType::Company => ["required", "exists:companies,id"],
                OrganizationType::University => ["required", "exists:universities,id"],
                null => ["nullable"],
            },
        ];
    }

    public function getRole(): UserRole
    {
        return UserRole::from($this->validated("role"));
    }

    public function getOrganizationId(): ?string
    {
        return $this->validated("organization_id");
    }
}
