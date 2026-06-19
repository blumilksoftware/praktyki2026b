<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityTest extends TestCase
{
    use RefreshDatabase;

    public function testNeedingVerificationScopeOnlyReturnsPendingUniversities(): void
    {
        $pendingUniversity = University::factory()->pending()->create();
        $approvedUniversity = University::factory()->approved()->create();

        $results = University::needingVerification()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($pendingUniversity));
        $this->assertFalse($results->contains($approvedUniversity));
    }
}
