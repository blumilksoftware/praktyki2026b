<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\UnsaveOfferAction;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnsaveOfferActionTest extends TestCase
{
    use RefreshDatabase;

    private UnsaveOfferAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UnsaveOfferAction();
    }

    public function testStudentCanUnsaveOffer(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create();
        $student->favourites()->attach($offer->id);

        $this->action->execute($student, $offer);

        $this->assertFalse($student->favourites()->where("offer_id", $offer->id)->exists());
    }

    public function testUnsavingOfferNotInFavouritesIsANoop(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create();

        $this->action->execute($student, $offer);

        $this->assertFalse($student->favourites()->where("offer_id", $offer->id)->exists());
    }

    public function testUnsavingDeletedOfferRemovesItFromFavourites(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create();
        $student->favourites()->attach($offer->id);
        $offer->delete();

        $this->action->execute($student, $offer);

        $this->assertFalse($student->favourites()->where("offer_id", $offer->id)->exists());
    }
}
