<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;

class UploadStudentPhotoAction
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(User $user, UploadedFile $file): string
    {
        $path = $this->fileUploadService->upload(
            $file,
            "photos",
            $user->photo_path,
        );

        $user->update([
            "photo_path" => $path,
        ]);

        return $path;
    }
}
