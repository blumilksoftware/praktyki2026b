<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Admin\ChangeUserStatusAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockUserTokenInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserTokensAreInvalidatedWhenBlocked(): void
    {
        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
            "status" => UserStatus::Active,
        ]);
        $user = User::factory()->create(["status" => UserStatus::Active]);

        $user->createToken("test-token-1");
        $user->createToken("test-token-2");

        $this->assertCount(2, $user->tokens);

        (new ChangeUserStatusAction())->execute($admin, $user, UserStatus::Blocked);

        $this->assertCount(0, $user->fresh()->tokens);
        $this->assertEquals(UserStatus::Blocked, $user->fresh()->status);
    }

    public function testBlockedUserIsLoggedOut(): void
    {
        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
            "status" => UserStatus::Active,
        ]);
        $user = User::factory()->create(["status" => UserStatus::Active]);

        (new ChangeUserStatusAction())->execute($admin, $user, UserStatus::Blocked);

        $this->actingAs($user->fresh())
            ->get("/admin/dashboard")
            ->assertRedirect("/login")
            ->assertSessionHasErrors("email");
    }

    public function testUnblockingUserDoesNotRestoreTokens(): void
    {
        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
            "status" => UserStatus::Active,
        ]);
        $user = User::factory()->create(["status" => UserStatus::Active]);

        $user->createToken("test-token");

        (new ChangeUserStatusAction())->execute($admin, $user, UserStatus::Blocked);
        (new ChangeUserStatusAction())->execute($admin, $user->fresh(), UserStatus::Active);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
