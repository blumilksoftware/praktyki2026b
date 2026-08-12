<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            "member_search" => ["nullable", "string", "max:255"],
            "invitation_search" => ["nullable", "string", "max:255"],
            "search" => ["nullable", "string", "max:255"],
            "member_page" => ["nullable", "integer", "min:1"],
            "invitation_page" => ["nullable", "integer", "min:1"],
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:1", "max:100"],
        ];
    }

    public function getData(): array
    {
        $memberSearch = $this->string("member_search")->toString();
        $fallbackSearch = $this->string("search")->toString();
        $invitationSearch = $this->string("invitation_search")->toString();

        $memberSearch = trim($memberSearch !== "" ? $memberSearch : $fallbackSearch);
        $invitationSearch = trim($invitationSearch);

        $memberPage = $this->input("member_page", $this->input("page", 1));
        $invitationPage = $this->input("invitation_page", $this->input("page", 1));
        $perPage = $this->input("per_page", 10);

        return [
            "member_search" => filled($memberSearch) ? $memberSearch : "",
            "invitation_search" => filled($invitationSearch) ? $invitationSearch : "",
            "member_page" => max(1, (int)$memberPage),
            "invitation_page" => max(1, (int)$invitationPage),
            "per_page" => max(1, (int)$perPage),
        ];
    }

    protected function prepareForValidation(): void
    {
        $memberSearch = $this->input("member_search", $this->input("search", ""));
        $invitationSearch = $this->input("invitation_search", "");

        $memberSearch = is_string($memberSearch) ? trim($memberSearch) : "";
        $invitationSearch = is_string($invitationSearch) ? trim($invitationSearch) : "";

        $this->merge([
            "member_search" => $memberSearch,
            "invitation_search" => $invitationSearch,
        ]);
    }
}
