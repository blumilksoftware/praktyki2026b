<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\SaveOfferAction;
use App\Enums\OfferStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaveOfferActionTest extends TestCase
{
    use RefreshDatabase;

    private SaveOfferAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SaveOfferAction();
    }

    public function testStudentCanSaveActiveOffer(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);

        $this->action->execute($student, $offer);

        $this->assertTrue($student->favourites()->where("offer_id", $offer->id)->exists());
    }

    public function testSavingSameOfferTwiceIsRejected(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);

        $this->action->execute($student, $offer);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.already_saved"));

        $this->action->execute($student, $offer);
    }

    public function testSavingInactiveOfferIsRejected(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create(["status" => OfferStatus::Closed]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_inactive"));

        $this->action->execute($student, $offer);
    }
}
