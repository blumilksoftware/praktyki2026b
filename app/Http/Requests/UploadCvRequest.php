<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\MimeTypeByContentRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "cv" => [
                "required",
                "file",
                "max:5120",
                new MimeTypeByContentRule(["application/pdf"]),
            ],
        ];
    }
}
