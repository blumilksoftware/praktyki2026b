<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentPhotoTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = config("filesystems.default", "local");
        Storage::fake($this->disk);
    }

    public function testGuestCannotUploadPhoto(): void
    {
        $response = $this->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotUploadPhoto(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotUploadPhoto(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);

        $response = $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);

        $response->assertStatus(403);
    }

    public function testStudentCanUploadValidPhoto(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);

        $response->assertRedirect();
        $user->refresh();

        $this->assertNotNull($user->photo_path);
        Storage::disk($this->disk)->assertExists($user->photo_path);
    }

    public function testUploadFailsForInvalidMimeType(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent("photo.txt", "Plain text file content, not an image");

        $response = $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $file]);

        $response->assertInvalid("photo");
        $user->refresh();
        $this->assertNull($user->photo_path);
    }

    public function testUploadFailsWhenPhotoFileSizeExceedsLimit(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $file = UploadedFile::fake()->createWithContent(
            "photo.png",
            $this->validPhotoContent() . str_repeat("A", 2049 * 1024),
        );

        $response = $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $file]);

        $response->assertInvalid("photo");
        $user->refresh();
        $this->assertNull($user->photo_path);
    }

    public function testUploadingReplacementDeletesPreviousPhotoFromStorage(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);
        $user->refresh();
        $firstPath = $user->photo_path;

        Storage::disk($this->disk)->assertExists($firstPath);

        $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);
        $user->refresh();
        $secondPath = $user->photo_path;

        $this->assertNotEquals($firstPath, $secondPath);
        Storage::disk($this->disk)->assertExists($secondPath);
        Storage::disk($this->disk)->assertMissing($firstPath);
    }

    public function testGuestCannotDeletePhoto(): void
    {
        $response = $this->delete(route("student.profile.photo.delete"));

        $response->assertRedirect(route("login"));
    }

    public function testStudentCanDeleteTheirPhoto(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $this->validPhoto()]);
        $user->refresh();
        $photoPath = $user->photo_path;

        Storage::disk($this->disk)->assertExists($photoPath);

        $response = $this->actingAs($user)->delete(route("student.profile.photo.delete"));
        $response->assertRedirect();
        $user->refresh();

        $this->assertNull($user->photo_path);
        Storage::disk($this->disk)->assertMissing($photoPath);
    }

    public function testDeletingPhotoWhenNoneExistsIsNoop(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "photo_path" => null,
        ]);

        $response = $this->actingAs($user)->delete(route("student.profile.photo.delete"));

        $response->assertRedirect();
        $this->assertNull($user->fresh()->photo_path);
    }

    private function validPhoto(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent("photo.png", $this->validPhotoContent());
    }

    private function validPhotoContent(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
