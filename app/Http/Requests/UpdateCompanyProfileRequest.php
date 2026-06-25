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
        ];
    }

    public function getData(): array
    {
        return [
            "logo" => $this->file("logo"),
            "description" => $this->string("description")->toString() ?: null,
            "tags" => $this->input("tags"),
        ];
    }
}
