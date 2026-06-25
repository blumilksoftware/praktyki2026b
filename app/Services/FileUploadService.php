<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FileUploadService
{
    /**
     * Upload a file to the configured storage disk and optionally delete a previous file.
     */
    public function upload(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $disk = config("uploads.disk", "local");

        if ($oldPath !== null) {
            $this->delete($oldPath);
        }

        $path = Storage::disk($disk)->putFile($directory, $file);

        if ($path === false) {
            throw new RuntimeException("Failed to store the uploaded file.");
        }

        return $path;
    }

    /**
     * Delete a file from the configured storage disk.
     */
    public function delete(string $path): bool
    {
        $disk = config("uploads.disk", "local");

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}
