<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockUserTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotBlockUser(): void
    {
        $target = User::factory()->create(["status" => UserStatus::Active]);

        $this->patch("/admin/users/{$target->id}/status", ["status" => UserStatus::Blocked->value])
            ->assertStatus(401);

        $this->assertEquals(UserStatus::Active, $target->fresh()->status);
    }

    public function testNonSuperAdminCannotBlockUser(): void
    {
        $admin = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $target = User::factory()->create(["status" => UserStatus::Active]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", ["status" => UserStatus::Blocked->value])
            ->assertStatus(403);

        $this->assertEquals(UserStatus::Active, $target->fresh()->status);
    }

    public function testSuperAdminCanBlockUser(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["status" => UserStatus::Active]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", ["status" => UserStatus::Blocked->value])
            ->assertRedirect();

        $this->assertEquals(UserStatus::Blocked, $target->fresh()->status);
    }

    public function testSuperAdminCanUnblockUser(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["status" => UserStatus::Blocked]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", ["status" => UserStatus::Active->value])
            ->assertRedirect();

        $this->assertEquals(UserStatus::Active, $target->fresh()->status);
    }

    public function testSuperAdminCannotBlockThemselves(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}/status", ["status" => UserStatus::Blocked->value])
            ->assertForbidden();

        $this->assertEquals(UserStatus::Active, $admin->fresh()->status);
    }

    public function testStatusOutsideActiveAndBlockedIsRejected(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["status" => UserStatus::Active]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", ["status" => UserStatus::Deleted->value])
            ->assertSessionHasErrors("status");

        $this->assertEquals(UserStatus::Active, $target->fresh()->status);
    }

    public function testBlockedUserCannotLogIn(): void
    {
        $user = User::factory()->create([
            "email" => "blocked@example.com",
            "password" => "password",
            "role" => UserRole::Student,
            "status" => UserStatus::Blocked,
            "email_verified_at" => now(),
        ]);

        $this->post("/login", [
            "email" => $user->email,
            "password" => "password",
        ])->assertSessionHasErrors("email");

        $this->assertGuest();
    }

    public function testBlockedSuperAdminCannotLogInToAdminPanel(): void
    {
        $admin = User::factory()->create([
            "email" => "blockedadmin@example.com",
            "password" => "password",
            "role" => UserRole::SuperAdmin,
            "status" => UserStatus::Blocked,
            "email_verified_at" => now(),
        ]);

        $this->post("/admin/login", [
            "email" => $admin->email,
            "password" => "password",
        ])->assertSessionHasErrors("email");

        $this->assertGuest();
    }

    public function testBlockedUserWithActiveSessionIsLoggedOut(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($user)->get("/student/dashboard")->assertOk();

        $user->forceFill(["status" => UserStatus::Blocked])->save();

        $this->get("/student/dashboard")->assertRedirect(route("login"));
        $this->assertGuest();
    }
}
