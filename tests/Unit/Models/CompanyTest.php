<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function testNeedingVerificationScopeOnlyReturnsPendingCompanies(): void
    {
        $pendingCompany = Company::factory()->pending()->create();
        $approvedCompany = Company::factory()->approved()->create();

        $results = Company::needingVerification()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($pendingCompany));
        $this->assertFalse($results->contains($approvedCompany));
    }
}
