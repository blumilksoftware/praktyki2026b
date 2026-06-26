<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\DomainRule;
use App\Rules\MimeTypeByContentRule;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUniversityProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $university = $this->user()?->universityOrganization;
        $universityId = $university?->id;

        return [
            "domain" => [
                "required",
                "string",
                "max:255",
                new DomainRule(),
                Rule::unique("universities", "domain")->ignore($universityId),
                function (string $attribute, mixed $value, Closure $fail) use ($university): void {
                    if ($university && $university->domain !== null && $university->domain !== "" && $university->domain !== $value) {
                        $fail("validation.university_domain_locked")->translate();
                    }
                },
            ],
            "logo" => [
                "nullable",
                "file",
                "max:2048",
                new MimeTypeByContentRule(["image/jpeg", "image/png", "image/webp"]),
            ],
            "external_form_url" => ["nullable", "string", "url", "max:255"],
            "faculties" => ["nullable", "array"],
            "faculties.*.name" => ["required", "string", "max:255"],
            "faculties.*.study_fields" => ["required", "array"],
            "faculties.*.study_fields.*" => ["required", "string", "max:255"],
        ];
    }

    public function getData(): array
    {
        return [
            "domain" => $this->string("domain")->toString(),
            "logo" => $this->file("logo"),
            "external_form_url" => $this->string("external_form_url")->toString() ?: null,
            "faculties" => $this->has("faculties") ? $this->input("faculties") : null,
        ];
    }
}
