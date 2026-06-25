<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\MimeTypeByContentRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MimeTypeByContentRuleTest extends TestCase
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

    public function testRulePassesForValidPdf(): void
    {
        $filePath = $this->createTempFile("%PDF-1.4 ...");
        $file = new UploadedFile($filePath, "document.pdf", "application/pdf", null, true);

        $rule = new MimeTypeByContentRule(["application/pdf"]);

        $validator = Validator::make(
            ["file" => $file],
            ["file" => [$rule]],
        );

        $this->assertTrue($validator->passes());
    }

    public function testRulePassesForValidPng(): void
    {
        $pngBase64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==";
        $filePath = $this->createTempFile(base64_decode($pngBase64, true));
        $file = new UploadedFile($filePath, "image.png", "image/png", null, true);

        $rule = new MimeTypeByContentRule(["image/png"]);

        $validator = Validator::make(
            ["file" => $file],
            ["file" => [$rule]],
        );

        $this->assertTrue($validator->passes());
    }

    public function testRuleFailsForInvalidMimeType(): void
    {
        $filePath = $this->createTempFile("%PDF-1.4 ...");
        $file = new UploadedFile($filePath, "document.pdf", "application/pdf", null, true);

        $rule = new MimeTypeByContentRule(["image/png"]);

        $validator = Validator::make(
            ["file" => $file],
            ["file" => [$rule]],
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey("file", $validator->errors()->toArray());
        $this->assertEquals(
            "Pole file musi być plikiem typu: image/png.",
            $validator->errors()->first("file"),
        );
    }

    public function testRuleFailsForSpoofedExtension(): void
    {
        $filePath = $this->createTempFile("Just some plain text content, not an image.");
        $file = new UploadedFile($filePath, "exploit.png", "image/png", null, true);

        $rule = new MimeTypeByContentRule(["image/png"]);

        $validator = Validator::make(
            ["file" => $file],
            ["file" => [$rule]],
        );

        $this->assertFalse($validator->passes());
    }

    public function testRuleFailsForNonUploadedFileInstance(): void
    {
        $rule = new MimeTypeByContentRule(["image/png"]);

        $validator = Validator::make(
            ["file" => "not-a-file-instance"],
            ["file" => [$rule]],
        );

        $this->assertFalse($validator->passes());
    }

    private function createTempFile(string $content): string
    {
        $filePath = tempnam(sys_get_temp_dir(), "mime_test");
        file_put_contents($filePath, $content);
        $this->tempFiles[] = $filePath;

        return $filePath;
    }
}
