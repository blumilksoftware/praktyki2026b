<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get("/test-abort/{status}", function (int $status): never {
            abort($status);
        });
    }

    public function testInertia404ReturnsErrorComponent(): void
    {
        $this->get("/test-abort/404", ["X-Inertia" => "true"])
            ->assertStatus(404)
            ->assertJsonPath("component", "Errors/Error")
            ->assertJsonPath("props.status", 404);
    }

    public function testJson404ReturnsJsonMessage(): void
    {
        $this->get("/this-page-does-not-exist", ["Accept" => "application/json"])
            ->assertStatus(404)
            ->assertJson(["message" => "Not Found"]);
    }

    public function testInertia403ReturnsErrorComponent(): void
    {
        $this->get("/test-abort/403", ["X-Inertia" => "true"])
            ->assertStatus(403)
            ->assertJsonPath("component", "Errors/Error")
            ->assertJsonPath("props.status", 403);
    }

    public function testJson403ReturnsJsonMessage(): void
    {
        $this->get("/test-abort/403", ["Accept" => "application/json"])
            ->assertStatus(403)
            ->assertJson(["message" => "Forbidden"]);
    }

    public function testInertia401ReturnsErrorComponent(): void
    {
        $this->get("/test-abort/401", ["X-Inertia" => "true"])
            ->assertStatus(401)
            ->assertJsonPath("component", "Errors/Error")
            ->assertJsonPath("props.status", 401);
    }

    public function testJson401ReturnsJsonMessage(): void
    {
        $this->get("/test-abort/401", ["Accept" => "application/json"])
            ->assertStatus(401)
            ->assertJson(["message" => "Unauthorized"]);
    }

    public function testErrorPageIncludesRoleForAuthenticatedUser(): void
    {
        $user = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($user)
            ->get("/test-abort/403", ["X-Inertia" => "true"])
            ->assertStatus(403)
            ->assertJsonPath("props.role", "student");
    }

    public function testErrorPageRoleIsNullForGuest(): void
    {
        $this->get("/test-abort/401", ["X-Inertia" => "true"])
            ->assertStatus(401)
            ->assertJsonPath("props.role", null);
    }

    public function testFallbackRouteReturns404(): void
    {
        $this->get("/completely/unknown/path")
            ->assertStatus(404);
    }
}
