<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsureCompanyIsVerifiedTest extends TestCase
{
    public function testRedirectsPendingCompanyAdminToVerificationPage(): void
    {
        $user = User::factory()->make([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Pending,
        ]);

        $company = new Company();
        $company->forceFill([
            "verification_status" => VerificationStatus::Pending,
        ]);

        $user->setRelation("company", $company);

        $request = new Request();
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureCompanyIsVerified();
        $called = false;

        $response = $middleware->handle($request, function () use (&$called): string {
            $called = true;

            return "OK";
        });

        $this->assertFalse($called);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route("company.verification.pending"), $response->getTargetUrl());
    }
}
