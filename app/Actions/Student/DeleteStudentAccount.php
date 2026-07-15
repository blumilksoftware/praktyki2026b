<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Support\Str;

class DeleteStudentAccount
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(User $student): void
    {
        if ($student->photo_path !== null) {
            $this->fileUploadService->delete($student->photo_path);
        }

        if ($student->cv_path !== null) {
            $this->fileUploadService->delete($student->cv_path);
        }

        $student->forceFill([
            "first_name" => null,
            "last_name" => null,
            "photo_path" => null,
            "cv_path" => null,
            "pending_email" => null,
            "email" => "deleted-" . $student->id . "-" . Str::random(8) . "@deleted.local",
            "status" => UserStatus::Deleted,
        ])->save();
    }
}
