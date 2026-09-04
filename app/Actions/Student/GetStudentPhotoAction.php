<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetStudentPhotoAction
{
    public function execute(User $user): StreamedResponse
    {
        if ($user->photo_path === null) {
            throw new NotFoundHttpException();
        }

        if (!Storage::disk("local")->exists($user->photo_path)) {
            throw new NotFoundHttpException();
        }

        return Storage::disk("local")->response($user->photo_path);
    }
}
