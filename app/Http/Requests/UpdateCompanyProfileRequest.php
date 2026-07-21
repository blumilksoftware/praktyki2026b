<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\MimeTypeByContentRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "logo" => [
                "nullable",
                "file",
                "max:2048",
                new MimeTypeByContentRule(["image/jpeg", "image/png", "image/webp"]),
            ],
            "description" => ["nullable", "string", "max:5000"],
            "tags" => ["nullable", "array", "max:20"], 
            "tags.*" => ["string", "max:50"], 
            "website" => ["nullable", "string", "max:255"],

            "phone" => ["required", "string", "max:50"],
            "street" => ["required", "string", "max:255"],
            "buildingNumber" => ["required", "string", "max:50"],
            "postalCode" => ["required", "string", "max:20"],
            "city" => ["required", "string", "max:255"],
            "nip" => ["required", "string", "max:50"],
        ];
    }

    public function getData(): array
    {
        $tags = $this->input("tags");

        return [
            "logo" => $this->file("logo"),
            "description" => $this->string("description")->toString() ?: null,
            "tags" => empty($tags) ? null : (is_array($tags) ? $tags : []), 
            "website" => $this->string("website")->toString() ?: null,
            "phone" => $this->string("phone")->toString() ?: null,
            "street" => $this->string("street")->toString() ?: null,
            "building_number" => $this->string("buildingNumber")->toString() ?: null,
            "postal_code" => $this->string("postalCode")->toString() ?: null,
            "city" => $this->string("city")->toString() ?: null,
            "nip" => $this->string("nip")->toString() ?: null,
        ];
    }
}
