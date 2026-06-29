<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\DeleteCvAction;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteCvActionTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteDeletesCvAndClearsUserPath(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $user = User::factory()->create([
            "cv_path" => "cvs/test.pdf",
        ]);

        Storage::disk($disk)->put("cvs/test.pdf", "%PDF-1.4 test");
        Storage::disk($disk)->assertExists("cvs/test.pdf");

        $action = new DeleteCvAction(new FileUploadService());
        $action->execute($user);

        $this->assertNull($user->refresh()->cv_path);
        Storage::disk($disk)->assertMissing("cvs/test.pdf");
    }
}
