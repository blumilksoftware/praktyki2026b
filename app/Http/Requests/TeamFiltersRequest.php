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
        $memberSearch = trim((string)($this->validated("member_search") ?? ""));
        $invitationSearch = trim((string)($this->validated("invitation_search") ?? ""));
        $memberPage = $this->validated("member_page") ?? 1;
        $invitationPage = $this->validated("invitation_page") ?? 1;
        $perPage = $this->validated("per_page") ?? 10;

        return [
            "member_search" => $memberSearch,
            "invitation_search" => $invitationSearch,
            "member_search_lower" => mb_strtolower($memberSearch),
            "invitation_search_lower" => mb_strtolower($invitationSearch),
            "member_page" => $memberPage,
            "invitation_page" => $invitationPage,
            "per_page" => $perPage,
        ];
    }

    protected function prepareForValidation(): void
    {
        $memberSearch = $this->input("member_search", $this->input("search", ""));
        $invitationSearch = $this->input("invitation_search", "");
        $memberPage = (int)$this->input("member_page", $this->input("page", 1));
        $invitationPage = (int)$this->input("invitation_page", $this->input("page", 1));
        $perPage = (int)$this->input("per_page", 10);

        $this->merge([
            "member_search" => is_string($memberSearch) ? trim($memberSearch) : "",
            "invitation_search" => is_string($invitationSearch) ? trim($invitationSearch) : "",
            "member_page" => $memberPage,
            "invitation_page" => $invitationPage,
            "per_page" => $perPage,
        ]);
    }
}
