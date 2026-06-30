<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Middleware\EnsureStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStudentTest extends TestCase
{
    public function testAllowsActiveStudent(): void
    {
        $user = User::factory()->make([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $request = new Request();
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureStudent();
        $called = false;

        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response("OK");
        });

        $this->assertTrue($called);
        $this->assertEquals("OK", $response->getContent());
    }

    public function testAbortsForGuest(): void
    {
        $request = new Request();

        $middleware = new EnsureStudent();

        $this->expectException(HttpException::class);

        try {
            $middleware->handle($request, fn() => response("OK"));
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());

            throw $e;
        }
    }

    public function testAbortsForNonStudentRole(): void
    {
        $user = User::factory()->make([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $request = new Request();
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureStudent();

        $this->expectException(HttpException::class);

        try {
            $middleware->handle($request, fn() => response("OK"));
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());

            throw $e;
        }
    }

    public function testAbortsForInactiveStudent(): void
    {
        $user = User::factory()->make([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);

        $request = new Request();
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureStudent();

        $this->expectException(HttpException::class);

        try {
            $middleware->handle($request, fn() => response("OK"));
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());

            throw $e;
        }
    }
}
