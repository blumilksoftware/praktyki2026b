<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\NipRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CompanyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "company_name" => ["required", "string", "max:255"],
            "nip" => ["required", "string", new NipRule(), Rule::unique("companies", "nip")],
            "email" => ["required", "string", "email", "max:255", Rule::unique("users", "email")],
            "password" => ["required", "confirmed", Password::defaults()],
            "street" => ["required", "string", "max:255"],
            "building_number" => ["required", "string", "regex:/^\d+$/", "max:50"],
            "postal_code" => ["required", "string", "regex:/^\d{2}-\d{3}$/"],
            "city" => ["required", "string", "max:255"],
            "phone" => ["required", "string", "max:20", new PhoneRule()],
            "website" => ["nullable", "string", "url", "max:255"],
            "terms" => ["required", "accepted"],
        ];
    }

    public function messages(): array
    {
        return [
            "email.unique" => __("validation.email_taken"),
            "email.email" => __("validation.email_invalid_friendly"),
            "postal_code.regex" => __("validation.postal_code_format"),
            "website.url" => __("validation.website_invalid_friendly"),
            "building_number.regex" => __("validation.building_number_digits"),
        ];
    }

    protected function prepareForValidation(): void
    {
        $postalCode = preg_replace("/\s+/", "", (string)$this->input("postal_code"));
        if (preg_match("/^\d{5}$/", $postalCode) === 1) {
            $postalCode = substr($postalCode, 0, 2) . "-" . substr($postalCode, 2);
        }

        $phone = preg_replace("/[\s-]+/", "", (string)$this->input("phone"));
        if (str_starts_with($phone, "0048")) {
            $phone = "+" . substr($phone, 2);
        }
        if (preg_match("/^48\d{9}$/", $phone) === 1) {
            $phone = "+" . $phone;
        }
        if (preg_match("/^[1-9]\d{8}$/", $phone) === 1) {
            $phone = "+48" . $phone;
        }

        $website = trim((string)$this->input("website"));
        if ($website !== "" && preg_match("/^https?:\/\//i", $website) !== 1) {
            $website = "https://" . $website;
        }

        $this->merge([
            "postal_code" => $postalCode,
            "phone" => $phone,
            "website" => $website,
        ]);
    }

    public function getData(): array
    {
        return [
            "company_name" => $this->string("company_name")->toString(),
            "nip" => $this->string("nip")->toString(),
            "email" => $this->string("email")->toString(),
            "password" => $this->string("password")->toString(),
            "street" => $this->string("street")->toString(),
            "building_number" => $this->string("building_number")->toString(),
            "postal_code" => $this->string("postal_code")->toString(),
            "city" => $this->string("city")->toString(),
            "phone" => $this->string("phone")->toString(),
            "website" => $this->string("website")->toString() ?: null,
        ];
    }
}
