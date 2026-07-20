<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Enums\OfferStatus;
use App\Events\Offer\OfferBecameUnavailable;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OfferObserverTest extends TestCase
{
    use RefreshDatabase;

    public function testClosingOfferDispatchesEventWithClosedReason(): void
    {
        Event::fake([OfferBecameUnavailable::class]);

        $offer = Offer::factory()->published()->create();
        $offer->update(["status" => OfferStatus::Closed]);

        Event::assertDispatched(
            OfferBecameUnavailable::class,
            fn(OfferBecameUnavailable $event): bool => $event->offer->is($offer) && $event->reason === "closed",
        );
    }

    public function testExpiringOfferDispatchesEventWithExpiredReason(): void
    {
        Event::fake([OfferBecameUnavailable::class]);

        $offer = Offer::factory()->published()->create();
        $offer->update(["status" => OfferStatus::Expired]);

        Event::assertDispatched(
            OfferBecameUnavailable::class,
            fn(OfferBecameUnavailable $event): bool => $event->offer->is($offer) && $event->reason === "expired",
        );
    }

    public function testPublishingOfferSetsPublishedAt(): void
    {
        $offer = Offer::factory()->draft()->create();

        $offer->update(["status" => OfferStatus::Published]);

        $this->assertNotNull($offer->fresh()->published_at);
    }

    public function testPublishedAtIsNotOverwrittenOnSubsequentSaves(): void
    {
        $offer = Offer::factory()->published()->create();
        $originalPublishedAt = $offer->published_at;

        $offer->update(["title" => "Updated Title"]);

        $this->assertTrue($originalPublishedAt->equalTo($offer->fresh()->published_at));
    }

    public function testUnrelatedFieldUpdateDoesNotDispatchEvent(): void
    {
        Event::fake([OfferBecameUnavailable::class]);

        $offer = Offer::factory()->published()->create();
        $offer->update(["title" => "Updated Title"]);

        Event::assertNotDispatched(OfferBecameUnavailable::class);
    }

    public function testDeletingOfferDispatchesEventWithDeletedReason(): void
    {
        Event::fake([OfferBecameUnavailable::class]);

        $offer = Offer::factory()->published()->create();
        $offer->delete();

        Event::assertDispatched(
            OfferBecameUnavailable::class,
            fn(OfferBecameUnavailable $event): bool => $event->offer->is($offer) && $event->reason === "deleted",
        );
    }
}
