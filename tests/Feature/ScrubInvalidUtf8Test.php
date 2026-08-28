<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScrubInvalidUtf8Test extends TestCase
{
    use RefreshDatabase;

    public function testOffersPageRendersWhenSearchCarriesInvalidUtf8(): void
    {
        $response = $this->get("/offers?search=%C0%AEwindows.ini");

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("filters.search", "??windows.ini"),
            );

        $this->assertStringNotContainsString('data-page=""', $response->getContent());
    }

    public function testAdminUsersPageRendersWhenSearchCarriesInvalidUtf8(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $response = $this->actingAs($admin)->get("/admin/users?search=%C0%AEwindows.ini");

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Users")
                    ->where("filters.search", "??windows.ini"),
            );

        $this->assertStringNotContainsString('data-page=""', $response->getContent());
    }

    public function testValidMultibyteSearchIsLeftUntouched(): void
    {
        $this->get("/offers?" . http_build_query(["search" => "Gdańsk"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("filters.search", "Gdańsk"),
            );
    }
}
