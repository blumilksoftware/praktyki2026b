<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use App\Services\FileUploadService;

class DeleteStudentPhotoAction
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(User $user): void
    {
        if ($user->photo_path !== null) {
            $this->fileUploadService->delete($user->photo_path);
            $user->update([
                "photo_path" => null,
            ]);
        }
    }
}
