<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "rating" => ["required", "integer", "min:1", "max:5"],
            "comment" => ["nullable", "string", "max:1000"],
        ];
    }

    public function attributes(): array
    {
        return [
            "rating" => __("validation.attributes.rating"),
            "comment" => __("validation.attributes.comment"),
        ];
    }
}
