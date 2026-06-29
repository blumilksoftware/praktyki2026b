<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FileUploadService
{
    public function upload(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $disk = config("filesystems.default", "local");

        if ($oldPath !== null) {
            $this->delete($oldPath);
        }

        $path = Storage::disk($disk)->putFile($directory, $file);

        if ($path === false) {
            throw new RuntimeException("Failed to store the uploaded file.");
        }

        return $path;
    }

    public function delete(string $path): bool
    {
        $disk = config("filesystems.default", "local");

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}
