<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\PublishOffer;
use App\Enums\OfferStatus;
use App\Models\Company;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublishOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testItPublishesDraftOfferOfVerifiedCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->draft()->create(["company_id" => $company->id]);

        $action = new PublishOffer();
        $result = $action->execute($offer);

        $this->assertEquals(OfferStatus::Published, $result->status);
        $this->assertNotNull($result->published_at);
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Published->value,
        ]);
    }

    public function testItRejectsPublishingWhenCompanyIsNotVerified(): void
    {
        $company = Company::factory()->pending()->create();
        $offer = Offer::factory()->draft()->create(["company_id" => $company->id]);

        $action = new PublishOffer();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_publish_requires_verification"));

        $action->execute($offer);
    }

    public function testItRejectsPublishingNonDraftOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->closed()->create(["company_id" => $company->id]);

        $action = new PublishOffer();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_publish_invalid_status"));

        $action->execute($offer);
    }
}
