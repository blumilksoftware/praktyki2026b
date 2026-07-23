<?php

declare(strict_types=1);

namespace Tests\Unit\University;

use App\Actions\University\ResolveUniversityByDomain;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveUniversityByDomainTest extends TestCase
{
    use RefreshDatabase;

    public function testItResolvesUniversityByExactDomainMatch(): void
    {
        $university = University::factory()->create(["domain" => "example.com"]);

        $result = (new ResolveUniversityByDomain())->execute("student@example.com");

        $this->assertNotNull($result);
        $this->assertEquals($university->id, $result->id);
    }

    public function testItResolvesUniversityBySubdomain(): void
    {
        $university = University::factory()->create(["domain" => "example.com"]);

        $result = (new ResolveUniversityByDomain())->execute("student@sub.example.com");

        $this->assertNotNull($result);
        $this->assertEquals($university->id, $result->id);
    }

    public function testItReturnsNullWhenNoUniversityMatchesDomain(): void
    {
        University::factory()->create(["domain" => "example.com"]);

        $result = (new ResolveUniversityByDomain())->execute("student@unrelated.com");

        $this->assertNull($result);
    }
}
