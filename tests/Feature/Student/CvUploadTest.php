<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CvUploadTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = config("filesystems.default", "local");
        Storage::fake($this->disk);
    }

    public function testGuestCannotUploadCv(): void
    {
        $file = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 test");

        $response = $this->post(route("student.cv.upload"), ["cv" => $file]);

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotUploadCv(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 test");

        $response = $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file]);

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotUploadCv(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);

        $file = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 test");

        $response = $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file]);

        $response->assertStatus(403);
    }

    public function testStudentCanUploadValidPdfCv(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 test content");

        $response = $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file]);

        $response->assertRedirect();
        $user->refresh();

        $this->assertNotNull($user->cv_path);
        Storage::disk($this->disk)->assertExists($user->cv_path);
    }

    public function testUploadFailsForInvalidMimeType(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent("cv.txt", "Plain text file content, not PDF");

        $response = $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file]);

        $response->assertInvalid("cv");
        $user->refresh();
        $this->assertNull($user->cv_path);
    }

    public function testUploadFailsWhenCvFileSizeExceedsLimit(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent(
            "cv.pdf",
            "%PDF-1.4 " . str_repeat("A", 5121 * 1024),
        );

        $response = $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file]);

        $response->assertInvalid("cv");
        $user->refresh();
        $this->assertNull($user->cv_path);
    }

    public function testUploadExceedingPostMaxSizeRendersApplicationErrorPage(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)
            ->withServerVariables(["CONTENT_LENGTH" => (string)(1024 * 1024 * 1024)])
            ->post(route("student.cv.upload"));

        $response->assertStatus(413);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component("Errors/Error")
                ->where("status", 413),
        );
        $user->refresh();
        $this->assertNull($user->cv_path);
    }

    public function testUploadingReplacementDeletesPreviousCvFromStorage(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file1 = UploadedFile::fake()->createWithContent("cv1.pdf", "%PDF-1.4 first");
        $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file1]);
        $user->refresh();
        $firstPath = $user->cv_path;

        Storage::disk($this->disk)->assertExists($firstPath);

        $file2 = UploadedFile::fake()->createWithContent("cv2.pdf", "%PDF-1.4 second");
        $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file2]);
        $user->refresh();
        $secondPath = $user->cv_path;

        $this->assertNotEquals($firstPath, $secondPath);
        Storage::disk($this->disk)->assertExists($secondPath);
        Storage::disk($this->disk)->assertMissing($firstPath);
    }

    public function testStudentCanDeleteTheirCv(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 to delete");
        $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $file]);
        $user->refresh();
        $cvPath = $user->cv_path;

        Storage::disk($this->disk)->assertExists($cvPath);

        $response = $this->actingAs($user)->delete(route("student.cv.delete"));
        $response->assertRedirect();
        $user->refresh();

        $this->assertNull($user->cv_path);
        Storage::disk($this->disk)->assertMissing($cvPath);
    }

    public function testStudentCanPreviewTheirCv(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => "cvs/test.pdf",
        ]);

        Storage::disk($this->disk)->put("cvs/test.pdf", "%PDF-1.4 preview");

        $response = $this->actingAs($user)->get(route("student.cv.preview"));

        $response->assertOk();
        $response->assertHeader("content-type", "application/pdf");
        $this->assertStringContainsString("inline", $response->headers->get("content-disposition"));
    }
}
