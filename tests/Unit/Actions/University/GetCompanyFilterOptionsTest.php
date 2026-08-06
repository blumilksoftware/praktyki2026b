<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\GetCompanyFilterOptions;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCompanyFilterOptionsTest extends TestCase
{
    use RefreshDatabase;

    private GetCompanyFilterOptions $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetCompanyFilterOptions();
    }

    public function testItReturnsUniqueSortedCitiesAndTagsFromVerifiedCompaniesOnly(): void
    {
        Company::factory()->approved()->create(["city" => "Wrocław", "tags" => ["Vue", "PHP"]]);
        Company::factory()->approved()->create(["city" => "Legnica", "tags" => ["PHP", "Laravel"]]);
        Company::factory()->pending()->create(["city" => "Poznań", "tags" => ["React"]]);

        $options = $this->action->execute();

        $this->assertEquals(["Legnica", "Wrocław"], $options["cities"]);
        $this->assertEquals(["Laravel", "PHP", "Vue"], $options["tags"]);
    }

    public function testItIgnoresCompaniesWithoutTags(): void
    {
        Company::factory()->approved()->create(["city" => "Kraków", "tags" => null]);

        $options = $this->action->execute();

        $this->assertEquals(["Kraków"], $options["cities"]);
        $this->assertEquals([], $options["tags"]);
    }
}
