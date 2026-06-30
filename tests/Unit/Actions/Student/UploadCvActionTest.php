<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\UploadCvAction;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCvActionTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteUploadsCvAndUpdatesUser(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 content");

        $action = new UploadCvAction(new FileUploadService());
        $path = $action->execute($user, $file);

        $this->assertNotEmpty($path);
        $this->assertEquals($path, $user->refresh()->cv_path);
        Storage::disk($disk)->assertExists($path);
    }
}
