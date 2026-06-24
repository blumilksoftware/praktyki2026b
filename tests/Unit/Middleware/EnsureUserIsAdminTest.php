<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserIsAdminTest extends TestCase
{
    private EnsureUserIsAdmin $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsureUserIsAdmin();
    }

    public function testUnauthenticatedRequestAborts401(): void
    {
        $request = Request::create("/admin/dashboard");

        try {
            $this->middleware->handle($request, fn() => new Response());
            $this->fail("Expected HttpException");
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
        }
    }

    public function testStudentRequestAborts403(): void
    {
        $this->assertNonAdminAborts403(UserRole::Student);
    }

    public function testCompanyAdminRequestAborts403(): void
    {
        $this->assertNonAdminAborts403(UserRole::CompanyAdmin);
    }

    public function testUniversityAdminRequestAborts403(): void
    {
        $this->assertNonAdminAborts403(UserRole::UniversityAdmin);
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
        });

        $this->assertTrue($nextCalled);
    }

    private function assertNonAdminAborts403(UserRole $role): void
    {
        $user = User::factory()->make(["role" => $role]);
        $request = Request::create("/admin/dashboard");
        $request->setUserResolver(fn() => $user);

        try {
            $this->middleware->handle($request, fn() => new Response());
            $this->fail("Expected HttpException");
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
