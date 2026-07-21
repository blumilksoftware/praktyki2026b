<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\BuildingNumberRule;
use App\Rules\DomainRule;
use App\Rules\PhoneRule;
use App\Rules\PostalCodeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UniversityRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "university_name" => ["required", "string", "max:255"],
            "email" => ["required", "string", "email", "max:255", Rule::unique("users", "email")],
            "domain" => ["required", "string", "max:255", new DomainRule(), Rule::unique("universities", "domain")],
            "password" => ["required", "confirmed", Password::defaults()],
            "street" => ["required", "string", "max:255"],
            "building_number" => ["required", "string", "max:10", new BuildingNumberRule()],
            "postal_code" => ["required", "string", new PostalCodeRule()],
            "city" => ["required", "string", "max:255"],
            "phone" => ["required", "string", new PhoneRule(), "max:20"],
            "website" => ["nullable", "string", "url", "max:255"],
            "terms" => ["required", "accepted"],
        ];
    }

    public function messages(): array
    {
        return [
            "email.unique" => __("validation.email_taken"),
            "email.email" => __("validation.email_invalid_friendly"),
            "website.url" => __("validation.website_invalid_friendly"),
            "password.confirmed" => __("validation.password_confirmed_friendly"),
            "terms.accepted" => __("validation.terms_accepted_friendly"),
        ];
    }

    public function getData(): array
    {
        return [
            "university_name" => $this->string("university_name")->toString(),
            "email" => $this->string("email")->toString(),
            "domain" => $this->string("domain")->toString(),
            "password" => $this->string("password")->toString(),
            "street" => $this->string("street")->toString(),
            "building_number" => $this->string("building_number")->toString(),
            "postal_code" => $this->string("postal_code")->toString(),
            "city" => $this->string("city")->toString(),
            "phone" => $this->string("phone")->toString(),
            "website" => $this->string("website")->toString() ?: null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            "postal_code" => $this->normalizePostalCode((string)$this->input("postal_code")),
            "phone" => $this->normalizePhone((string)$this->input("phone")),
            "website" => $this->normalizeWebsite((string)$this->input("website")),
        ]);
    }

    private function normalizePostalCode(string $postalCode): string
    {
        $postalCode = preg_replace("/\s+/", "", $postalCode);

        if (preg_match("/^\d{5}$/", $postalCode) === 1) {
            return substr($postalCode, 0, 2) . "-" . substr($postalCode, 2);
        }

        return $postalCode;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace("/[\s-]+/", "", $phone);

        if (str_starts_with($phone, "0048")) {
            $phone = "+" . substr($phone, 2);
        }

        if (preg_match("/^48\d{9}$/", $phone) === 1) {
            return "+" . $phone;
        }

        if (preg_match("/^[1-9]\d{8}$/", $phone) === 1) {
            return "+48" . $phone;
        }

        return $phone;
    }

    private function normalizeWebsite(string $website): string
    {
        $website = trim($website);

        if ($website !== "" && preg_match("/^https?:\/\//i", $website) !== 1) {
            return "https://" . $website;
        }

        return $website;
    }
}
