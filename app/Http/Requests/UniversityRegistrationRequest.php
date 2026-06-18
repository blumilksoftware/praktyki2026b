<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            "domain" => ["required", "string", "max:255", "regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i", Rule::unique("universities", "domain")],
            "password" => ["required", "confirmed", Password::defaults()],
            "street" => ["required", "string", "max:255"],
            "building_number" => ["required", "string", "max:50"],
            "postal_code" => ["required", "string", "max:10"],
            "city" => ["required", "string", "max:255"],
            "phone" => ["required", "string", "max:20"],
            "website" => ["nullable", "string", "url", "max:255"],
            "terms" => ["required", "accepted"],
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
}
