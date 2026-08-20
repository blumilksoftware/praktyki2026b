<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Offer;

use App\Actions\Offer\ExpireOffers;
use App\Enums\OfferStatus;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExpireOffersTest extends TestCase
{
    use RefreshDatabase;

    private ExpireOffers $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ExpireOffers();
    }

    public function testPublishedOfferPastEndDateBecomesExpired(): void
    {
        $offer = Offer::factory()->published()->create([
            "end_date" => now()->subDay(),
        ]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Expired, $offer->status);
    }

    public function testPublishedOfferEndingTodayIsUnaffected(): void
    {
        $offer = Offer::factory()->published()->create([
            "end_date" => today(),
        ]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Published, $offer->status);
    }

    public function testPublishedOfferNotPastEndDateIsUnaffected(): void
    {
        $offer = Offer::factory()->published()->create([
            "end_date" => now()->addDay(),
        ]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Published, $offer->status);
    }

    public function testNonPublishedOfferPastEndDateIsUnaffected(): void
    {
        $offer = Offer::factory()->closed()->create([
            "end_date" => now()->subDay(),
        ]);

        $this->action->execute();

        $offer->refresh();
        $this->assertEquals(OfferStatus::Closed, $offer->status);
    }

    public function testExpiringOfferNotifiesApplicants(): void
    {
        Mail::fake();

        $offer = Offer::factory()->published()->create([
            "end_date" => now()->subDay(),
        ]);
        $student = User::factory()->create();
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $student->id]);

        $this->action->execute();

        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($student->email) && $mail->reason === "expired",
        );
    }
}
