<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $application = $this->route("application");

        return [
            "status" => [
                "required",
                Rule::enum(ApplicationStatus::class)->except(ApplicationStatus::Pending),
                function (string $attribute, mixed $value, Closure $fail) use ($application): void {
                    if (!$application instanceof Application) {
                        return;
                    }

                    $newStatus = ApplicationStatus::tryFrom((string)$value);

                    if (!$newStatus) {
                        return;
                    }

                    if (!$application->status->canTransitionTo($newStatus)) {
                        $fail("validation.application_status_invalid_transition")->translate();
                    }
                },
            ],
        ];
    }
}
