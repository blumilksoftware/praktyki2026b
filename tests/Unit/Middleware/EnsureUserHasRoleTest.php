<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserHasRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserHasRoleTest extends TestCase
{
    private EnsureUserHasRole $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsureUserHasRole();
    }

    public function testUnauthenticatedRequestAborts401(): void
    {
        $request = Request::create("/admin/dashboard");

        try {
            $this->middleware->handle($request, fn() => new Response(), "superAdmin");
            $this->fail("Expected HttpException");
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
        }
    }

    public function testStudentRequestAborts403(): void
    {
        $this->assertRoleAborts403(UserRole::Student);
    }

    public function testCompanyAdminRequestAborts403(): void
    {
        $this->assertRoleAborts403(UserRole::CompanyAdmin);
    }

    public function testUniversityAdminRequestAborts403(): void
    {
        $this->assertRoleAborts403(UserRole::UniversityAdmin);
    }

    public function testSuperAdminRequestPassesThrough(): void
    {
        $user = User::factory()->make(["role" => UserRole::SuperAdmin]);
        $request = Request::create("/admin/dashboard");
        $request->setUserResolver(fn() => $user);
        $nextCalled = false;

        $this->middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;

            return new Response();
        }, "superAdmin");

        $this->assertTrue($nextCalled);
    }

    public function testMultipleRolesAllowsAnyMatch(): void
    {
        $user = User::factory()->make(["role" => UserRole::CompanyAdmin]);
        $request = Request::create("/dashboard");
        $request->setUserResolver(fn() => $user);
        $nextCalled = false;

        $this->middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;

            return new Response();
        }, "companyAdmin", "universityAdmin");

        $this->assertTrue($nextCalled);
    }

    public function testUnknownRoleThrowsInvalidArgumentException(): void
    {
        $user = User::factory()->make(["role" => UserRole::SuperAdmin]);
        $request = Request::create("/admin/dashboard");
        $request->setUserResolver(fn() => $user);

        $this->expectException(InvalidArgumentException::class);

        $this->middleware->handle($request, fn() => new Response(), "nonExistentRole");
    }

    private function assertRoleAborts403(UserRole $role): void
    {
        $user = User::factory()->make(["role" => $role]);
        $request = Request::create("/admin/dashboard");
        $request->setUserResolver(fn() => $user);

        try {
            $this->middleware->handle($request, fn() => new Response(), "superAdmin");
            $this->fail("Expected HttpException");
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
