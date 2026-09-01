<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestIsSentToLoginInsteadOfCrashing(): void
    {
        $this->get("/profile")->assertRedirect("/login");
        $this->get("/profile/edit")->assertRedirect("/login");
        $this->patch("/profile")->assertRedirect("/login");
        $this->get("/settings")->assertRedirect("/login");
    }

    public function testStudentIsRedirectedToOwnProfile(): void
    {
        $student = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($student)
            ->get("/profile")
            ->assertRedirect(route("student.profile"));
    }
}
