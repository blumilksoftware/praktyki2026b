<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\DeleteStudentAccount;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteStudentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function testItDeletesPhotoAndCvFilesWhenPresent(): void
    {
        $student = User::factory()->create([
            "photo_path" => "photos/avatar.png",
            "cv_path" => "cvs/resume.pdf",
        ]);

        $fileUploadService = $this->createMock(FileUploadService::class);
        $fileUploadService->expects($this->exactly(2))
            ->method("delete")
            ->with($this->callback(
                static fn (string $path): bool => in_array($path, ["photos/avatar.png", "cvs/resume.pdf"], true),
            ));

        (new DeleteStudentAccount($fileUploadService))->execute($student);
    }

    public function testItSkipsFileDeletionWhenPathsAreNull(): void
    {
        $student = User::factory()->create([
            "photo_path" => null,
            "cv_path" => null,
        ]);

        $fileUploadService = $this->createMock(FileUploadService::class);
        $fileUploadService->expects($this->never())->method("delete");

        (new DeleteStudentAccount($fileUploadService))->execute($student);
    }

    public function testItOnlyDeletesThePhotoWhenNoCvIsPresent(): void
    {
        $student = User::factory()->create([
            "photo_path" => "photos/avatar.png",
            "cv_path" => null,
        ]);

        $fileUploadService = $this->createMock(FileUploadService::class);
        $fileUploadService->expects($this->once())
            ->method("delete")
            ->with("photos/avatar.png");

        (new DeleteStudentAccount($fileUploadService))->execute($student);
    }

    public function testItAnonymizesStudentDataAndMarksAsDeleted(): void
    {
        $student = User::factory()->create([
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "photo_path" => "photos/avatar.png",
            "cv_path" => "cvs/resume.pdf",
            "pending_email" => "new-address@example.com",
            "email" => "jan.kowalski@example.com",
        ]);

        $fileUploadService = $this->createMock(FileUploadService::class);
        $fileUploadService->method("delete");

        (new DeleteStudentAccount($fileUploadService))->execute($student);

        $fresh = $student->fresh();

        $this->assertNull($fresh->first_name);
        $this->assertNull($fresh->last_name);
        $this->assertNull($fresh->photo_path);
        $this->assertNull($fresh->cv_path);
        $this->assertNull($fresh->pending_email);
        $this->assertSame(UserStatus::Deleted, $fresh->status);
        $this->assertStringStartsWith("deleted-" . $student->id . "-", $fresh->email);
        $this->assertStringEndsWith("@deleted.local", $fresh->email);
        $this->assertNotSame("jan.kowalski@example.com", $fresh->email);
    }

    public function testItGeneratesAUniqueEmailEachTimeItIsCalled(): void
    {
        $firstStudent = User::factory()->create();
        $secondStudent = User::factory()->create();

        $fileUploadService = $this->createMock(FileUploadService::class);
        $fileUploadService->method("delete");

        $action = new DeleteStudentAccount($fileUploadService);
        $action->execute($firstStudent);
        $action->execute($secondStudent);

        $this->assertNotSame($firstStudent->fresh()->email, $secondStudent->fresh()->email);
    }
}
