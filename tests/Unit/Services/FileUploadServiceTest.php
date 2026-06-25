<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        parent::tearDown();
    }

    public function testUploadStoresFileOnConfiguredDisk(): void
    {
        $disk = config("uploads.disk", "local");
        Storage::fake($disk);

        $filePath = $this->createTempFile("file content");
        $file = new UploadedFile($filePath, "document.txt", "text/plain", null, true);

        $service = new FileUploadService();
        $path = $service->upload($file, "documents");

        $this->assertNotEmpty($path);
        Storage::disk($disk)->assertExists($path);
    }

    public function testUploadDeletesPreviousFileWhenReplacementIsUploaded(): void
    {
        $disk = config("uploads.disk", "local");
        Storage::fake($disk);

        $service = new FileUploadService();

        // 1. Upload the first file
        $filePath1 = $this->createTempFile("first file content");
        $file1 = new UploadedFile($filePath1, "document1.txt", "text/plain", null, true);
        $path1 = $service->upload($file1, "documents");

        Storage::disk($disk)->assertExists($path1);

        // 2. Upload the replacement file
        $filePath2 = $this->createTempFile("second file content");
        $file2 = new UploadedFile($filePath2, "document2.txt", "text/plain", null, true);
        $path2 = $service->upload($file2, "documents", $path1);

        // The replacement file should exist, and the first file should be deleted from storage
        Storage::disk($disk)->assertExists($path2);
        Storage::disk($disk)->assertMissing($path1);
    }

    public function testDeleteRemovesFileFromDisk(): void
    {
        $disk = config("uploads.disk", "local");
        Storage::fake($disk);

        $filePath = $this->createTempFile("file content");
        $file = new UploadedFile($filePath, "document.txt", "text/plain", null, true);

        $service = new FileUploadService();
        $path = $service->upload($file, "documents");

        Storage::disk($disk)->assertExists($path);

        $deleted = $service->delete($path);

        $this->assertTrue($deleted);
        Storage::disk($disk)->assertMissing($path);
    }

    public function testDeleteReturnsFalseIfFileDoesNotExist(): void
    {
        $disk = config("uploads.disk", "local");
        Storage::fake($disk);

        $service = new FileUploadService();
        $deleted = $service->delete("non_existent_file.txt");

        $this->assertFalse($deleted);
    }

    private function createTempFile(string $content): string
    {
        $filePath = tempnam(sys_get_temp_dir(), "upload_test");
        file_put_contents($filePath, $content);
        $this->tempFiles[] = $filePath;

        return $filePath;
    }
}
