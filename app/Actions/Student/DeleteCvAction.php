<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use App\Services\FileUploadService;

class DeleteCvAction
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(User $user): void
    {
        if ($user->cv_path !== null) {
            $this->fileUploadService->delete($user->cv_path);
            $user->update([
                "cv_path" => null,
            ]);
        }
    }
}
