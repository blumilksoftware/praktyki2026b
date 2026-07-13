<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Events\Offer\OfferBecameUnavailable;
use App\Listeners\Offer\SendOfferUnavailableNotification;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOfferUnavailableNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function testQueuesOneMailPerApplicantStudent(): void
    {
        Mail::fake();

        $offer = Offer::factory()->published()->create();
        $firstStudent = User::factory()->create();
        $secondStudent = User::factory()->create();
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $firstStudent->id]);
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $secondStudent->id]);

        $listener = new SendOfferUnavailableNotification();
        $listener->handle(new OfferBecameUnavailable($offer, "closed"));

        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($firstStudent->email) && $mail->reason === "closed",
        );
        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($secondStudent->email) && $mail->reason === "closed",
        );
        Mail::assertQueuedCount(2);
    }

    public function testDoesNotQueueMailWhenOfferHasNoApplications(): void
    {
        Mail::fake();

        $offer = Offer::factory()->published()->create();

        $listener = new SendOfferUnavailableNotification();
        $listener->handle(new OfferBecameUnavailable($offer, "closed"));

        Mail::assertNothingQueued();
    }
}
