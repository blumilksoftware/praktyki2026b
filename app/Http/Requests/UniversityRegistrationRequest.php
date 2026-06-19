<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\DomainRule;
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
            "address" => ["required", "string", "max:255"],
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
            "address" => $this->string("address")->toString(),
            "phone" => $this->string("phone")->toString(),
            "website" => $this->string("website")->toString() ?: null,
        ];
    }
}
