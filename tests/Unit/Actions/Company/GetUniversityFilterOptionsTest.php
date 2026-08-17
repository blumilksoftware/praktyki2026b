<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\GetUniversityFilterOptions;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUniversityFilterOptionsTest extends TestCase
{
    use RefreshDatabase;

    private GetUniversityFilterOptions $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetUniversityFilterOptions();
    }

    public function testItReturnsUniqueCitiesFromVerifiedUniversitiesOnly(): void
    {
        University::factory()->approved()->create(["city" => "Wrocław"]);
        University::factory()->approved()->create(["city" => "Wrocław"]);
        University::factory()->approved()->create(["city" => "Legnica"]);
        University::factory()->pending()->create(["city" => "Poznań"]);

        $options = $this->action->execute();

        $this->assertEquals(["Legnica", "Wrocław"], $options["cities"]);
    }

    public function testItSortsPolishDiacriticsAlphabeticallyInsteadOfByByteValue(): void
    {
        University::factory()->approved()->create(["city" => "Zabrze"]);
        University::factory()->approved()->create(["city" => "Łódź"]);
        University::factory()->approved()->create(["city" => "Gdańsk"]);

        $options = $this->action->execute();

        $this->assertEquals(["Gdańsk", "Łódź", "Zabrze"], $options["cities"]);
    }
}
