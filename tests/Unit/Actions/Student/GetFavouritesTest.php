<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\GetFavourites;
use App\Enums\OfferStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetFavouritesTest extends TestCase
{
    use RefreshDatabase;

    private GetFavourites $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetFavourites();
    }

    public function testReturnsSavedOffersWithCurrentStatus(): void
    {
        $student = User::factory()->create();
        $published = Offer::factory()->create(["status" => OfferStatus::Published]);
        $closed = Offer::factory()->create(["status" => OfferStatus::Closed]);
        $expired = Offer::factory()->create(["status" => OfferStatus::Expired]);

        $student->favourites()->attach([$published->id, $closed->id, $expired->id]);

        $favourites = $this->action->execute($student);

        $this->assertCount(3, $favourites);
        $this->assertEqualsCanonicalizing(
            ["published", "closed", "expired"],
            array_column($favourites, "status"),
        );
    }

    public function testDeletedOfferRemainsInListWithDeletedAtSet(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);
        $student->favourites()->attach($offer->id);

        $offer->delete();

        $favourites = $this->action->execute($student);

        $this->assertCount(1, $favourites);
        $this->assertEquals($offer->id, $favourites[0]["id"]);
        $this->assertEquals("published", $favourites[0]["status"]);
        $this->assertNotNull($favourites[0]["deleted_at"]);
    }

    public function testNonDeletedOfferHasNullDeletedAt(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);
        $student->favourites()->attach($offer->id);

        $favourites = $this->action->execute($student);

        $this->assertNull($favourites[0]["deleted_at"]);
    }

    public function testDoesNotReturnOffersNotSavedByStudent(): void
    {
        $student = User::factory()->create();
        Offer::factory()->create(["status" => OfferStatus::Published]);

        $favourites = $this->action->execute($student);

        $this->assertCount(0, $favourites);
    }
}
