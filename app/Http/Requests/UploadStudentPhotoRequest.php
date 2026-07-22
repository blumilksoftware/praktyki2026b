<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\MimeTypeByContentRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadStudentPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "photo" => [
                "required",
                "file",
                "max:2048",
                new MimeTypeByContentRule(["image/jpeg", "image/png", "image/webp"]),
            ],
        ];
    }
}
