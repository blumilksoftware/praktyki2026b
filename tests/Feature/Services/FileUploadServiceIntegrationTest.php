<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceIntegrationTest extends TestCase
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

    public function testUploadUsesConfiguredDiskDynamically(): void
    {
        Storage::fake("local");
        Storage::fake("public");

        $filePath = $this->createTempFile("integration test content");
        $file = new UploadedFile($filePath, "integration.txt", "text/plain", null, true);

        $service = new FileUploadService();

        config(["filesystems.default" => "local"]);
        $path1 = $service->upload($file, "integrations");

        Storage::disk("local")->assertExists($path1);
        Storage::disk("public")->assertMissing($path1);

        config(["filesystems.default" => "public"]);
        $filePath2 = $this->createTempFile("integration test content 2");
        $file2 = new UploadedFile($filePath2, "integration2.txt", "text/plain", null, true);
        $path2 = $service->upload($file2, "integrations");

        Storage::disk("public")->assertExists($path2);
        Storage::disk("local")->assertMissing($path2);
    }

    private function createTempFile(string $content): string
    {
        $filePath = tempnam(sys_get_temp_dir(), "integration_test");
        file_put_contents($filePath, $content);
        $this->tempFiles[] = $filePath;

        return $filePath;
    }
}
