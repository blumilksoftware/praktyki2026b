<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = config("filesystems.default", "local");
        Storage::fake($this->disk);
    }

    public function testGuestCannotDeleteAccount(): void
    {
        $response = $this->delete(route("student.account.delete"), $this->validPayload());

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotDeleteAccount(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->delete(route("student.account.delete"), $this->validPayload());

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotDeleteAccount(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->delete(route("student.account.delete"), $this->validPayload());

        $response->assertStatus(403);
    }

    public function testChangeFailsWithWrongPassword(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->delete(route("student.account.delete"), $this->validPayload("wrong-password"));

        $response->assertInvalid("password");
        $this->assertEquals(UserStatus::Active, $user->fresh()->status);
    }

    public function testDeleteFailsWhenConfirmationIsMissing(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->delete(route("student.account.delete"), [
            "password" => "old-password",
        ]);

        $response->assertInvalid("confirmation");
        $this->assertEquals(UserStatus::Active, $user->fresh()->status);
    }

    public function testStudentCanDeleteTheirAccount(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "first_name" => "John",
            "last_name" => "Doe",
            "password" => "old-password",
        ]);
        $originalEmail = $user->email;

        $response = $this->actingAs($user)->delete(route("student.account.delete"), $this->validPayload());

        $response->assertRedirect("/");
        $user->refresh();

        $this->assertEquals(UserStatus::Deleted, $user->status);
        $this->assertNull($user->first_name);
        $this->assertNull($user->last_name);
        $this->assertNotEquals($originalEmail, $user->email);
        $this->assertStringEndsWith("@deleted.local", $user->email);
    }

    public function testDeletingAccountLogsOutTheSession(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $this->actingAs($user)->delete(route("student.account.delete"), $this->validPayload());

        $this->assertGuest();
        $this->assertNull(Auth::user());
    }

    public function testDeletingAccountRemovesStoredPhotoAndCv(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $photo = UploadedFile::fake()->createWithContent("photo.png", $this->validPhotoContent());
        $this->actingAs($user)->post(route("student.profile.photo.upload"), ["photo" => $photo]);

        $cv = UploadedFile::fake()->createWithContent("cv.pdf", "%PDF-1.4 test content");
        $this->actingAs($user)->post(route("student.cv.upload"), ["cv" => $cv]);

        $user->refresh();
        $photoPath = $user->photo_path;
        $cvPath = $user->cv_path;

        Storage::disk($this->disk)->assertExists($photoPath);
        Storage::disk($this->disk)->assertExists($cvPath);

        $this->actingAs($user)->delete(route("student.account.delete"), $this->validPayload());

        Storage::disk($this->disk)->assertMissing($photoPath);
        Storage::disk($this->disk)->assertMissing($cvPath);
        $user->refresh();
        $this->assertNull($user->photo_path);
        $this->assertNull($user->cv_path);
    }

    private function validPayload(string $password = "old-password"): array
    {
        return [
            "password" => $password,
            "confirmation" => true,
        ];
    }

    private function validPhotoContent(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
