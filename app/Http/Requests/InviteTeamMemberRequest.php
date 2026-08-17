<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\InvitationStatus;
use App\Enums\UserStatus;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class InviteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $organizationType = $user->role->organizationType();

        return $organizationType !== null
            && $user->role === $organizationType->adminRole()
            && $user->status === UserStatus::Active;
    }

    public function rules(): array
    {
        return [
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                $this->emailBelongsToAnotherOrganization(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "email.email" => __("validation.email_invalid_friendly"),
        ];
    }

    public function getData(): array
    {
        return [
            "email" => $this->string("email")->toString(),
        ];
    }

    private function emailBelongsToAnotherOrganization(): Closure
    {
        return function (string $attribute, string $value, Closure $fail): void {
            $email = strtolower(trim($value));
            $user = $this->user();

            if ($user === null || $user->organization_id === null) {
                return;
            }

            $existingUser = User::query()
                ->whereRaw("LOWER(email) = ?", [$email])
                ->first();

            if ($existingUser !== null) {
                if ($existingUser->organization_id === $user->organization_id) {
                    $fail(__("validation.email_taken"));

                    return;
                }

                $fail(__("validation.team_invite_other_organization"));

                return;
            }

            $existingInvitation = OrganizationInvitation::query()
                ->whereRaw("LOWER(email) = ?", [$email])
                ->where("status", InvitationStatus::Pending->value)
                ->first();

            if ($existingInvitation !== null && $existingInvitation->organization_id !== $user->organization_id) {
                $fail(__("validation.team_invite_other_organization"));
            }
        };
    }
}
