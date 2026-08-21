<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Offer;

use App\Actions\Offer\CloseFilledOffers;
use App\Enums\OfferStatus;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CloseFilledOffersTest extends TestCase
{
    use RefreshDatabase;

    private CloseFilledOffers $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CloseFilledOffers();
    }

    public function testPublishedOfferWithoutRemainingSpotsBecomesClosed(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 2]);
        Application::factory()->count(2)->accepted()->create(["offer_id" => $offer->id]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Closed, $offer->status);
        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $offer->id,
            "description" => "offer_closed_automatically",
        ]);
    }

    public function testPublishedOfferWithRemainingSpotsIsUnaffected(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 2]);
        Application::factory()->accepted()->create(["offer_id" => $offer->id]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Published, $offer->status);
    }

    public function testOfferWithMoreAcceptedApplicationsThanSpotsBecomesClosed(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 1]);
        Application::factory()->count(3)->accepted()->create(["offer_id" => $offer->id]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Closed, $offer->status);
    }

    public function testOnlyAcceptedApplicationsOccupySpots(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 1]);
        Application::factory()->pending()->create(["offer_id" => $offer->id]);
        Application::factory()->reviewed()->create(["offer_id" => $offer->id]);
        Application::factory()->rejected()->create(["offer_id" => $offer->id]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Published, $offer->status);
    }

    public function testNonPublishedOfferWithoutRemainingSpotsIsUnaffected(): void
    {
        $offer = Offer::factory()->draft()->create(["spots" => 1]);
        Application::factory()->accepted()->create(["offer_id" => $offer->id]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Draft, $offer->status);
    }

    public function testClosingOfferNotifiesApplicantsWhoDidNotGetASpot(): void
    {
        Mail::fake();

        $offer = Offer::factory()->published()->create(["spots" => 1]);
        $acceptedStudent = User::factory()->create();
        $rejectedStudent = User::factory()->create();
        Application::factory()->accepted()->create(["offer_id" => $offer->id, "student_id" => $acceptedStudent->id]);
        Application::factory()->pending()->create(["offer_id" => $offer->id, "student_id" => $rejectedStudent->id]);

        $this->action->execute();

        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($rejectedStudent->email) && $mail->reason === "closed",
        );
        Mail::assertNotQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($acceptedStudent->email),
        );
    }
}
