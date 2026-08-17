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
            "description" => ["nullable", "string", "max:2500"],
            "external_form_url" => ["nullable", "string", "url", "max:255"],
            "faculties" => ["nullable", "array"],
            "faculties.*.name" => ["required", "string", "max:255"],
            "faculties.*.study_fields" => ["required", "array"],
            "faculties.*.study_fields.*" => ["required", "string", "max:255"],
            "website" => ["nullable", "string", "url", "max:255"],
            "phone" => ["required", "string", "max:50"],
            "street" => ["required", "string", "max:255"],
            "postalCode" => ["required", "string", "max:20"],
            "city" => ["required", "string", "max:255"],
        ];
    }

    public function getData(): array
    {
        return [
            "domain" => $this->string("domain")->toString(),
            "logo" => $this->file("logo"),
            "description" => $this->string("description")->toString() ?: null,
            "external_form_url" => $this->string("external_form_url")->toString() ?: null,
            "faculties" => $this->has("faculties") ? $this->input("faculties") : null,
            "website" => $this->string("website")->toString() ?: null,
            "phone" => $this->string("phone")->toString(),
            "street" => $this->string("street")->toString(),
            "postal_code" => $this->string("postalCode")->toString(),
            "city" => $this->string("city")->toString(),
        ];
    }
}
