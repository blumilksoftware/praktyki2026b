<?php

declare(strict_types=1);

namespace Tests\Feature\Offer;

use App\Enums\OfferStatus;
use App\Models\Application;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivateOffersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCommandExpiresOutdatedOffersAndClosesFilledOnes(): void
    {
        $outdated = Offer::factory()->published()->create(["end_date" => now()->subDay()]);
        $filled = Offer::factory()->published()->create(["spots" => 1, "end_date" => now()->addMonth()]);
        $available = Offer::factory()->published()->create(["spots" => 2, "end_date" => now()->addMonth()]);
        Application::factory()->accepted()->create(["offer_id" => $filled->id]);
        Application::factory()->accepted()->create(["offer_id" => $available->id]);

        $this->artisan("offers:deactivate")
            ->expectsOutput("Filled offers closed: 1")
            ->expectsOutput("Outdated offers expired: 1")
            ->assertSuccessful();

        $this->assertEquals(OfferStatus::Expired, $outdated->refresh()->status);
        $this->assertEquals(OfferStatus::Closed, $filled->refresh()->status);
        $this->assertEquals(OfferStatus::Published, $available->refresh()->status);
    }

    public function testOfferBothOutdatedAndFilledBecomesClosed(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 1, "end_date" => now()->subDay()]);
        Application::factory()->accepted()->create(["offer_id" => $offer->id]);

        $this->artisan("offers:deactivate")
            ->expectsOutput("Filled offers closed: 1")
            ->expectsOutput("Outdated offers expired: 0")
            ->assertSuccessful();

        $this->assertEquals(OfferStatus::Closed, $offer->refresh()->status);
    }
}
