<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\WithdrawOfferAction;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WithdrawOfferActionTest extends TestCase
{
    use RefreshDatabase;

    private WithdrawOfferAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new WithdrawOfferAction();
    }

    public function testWithdrawalDeletesApplicationAndRemovesCvSnapshotAndIncrementsSpots(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);
        Storage::disk($disk)->put("applications/cvs/test_snapshot.pdf", "Snapshot content");

        $student = User::factory()->create();
        $offer = Offer::factory()->create(["spots" => 2]);

        $application = Application::factory()->create([
            "student_id" => $student->id,
            "offer_id" => $offer->id,
            "cv_path" => "applications/cvs/test_snapshot.pdf",
        ]);

        $this->action->execute($student, $offer);

        $this->assertDatabaseMissing("applications", [
            "id" => $application->id,
        ]);

        Storage::disk($disk)->assertMissing("applications/cvs/test_snapshot.pdf");
        $this->assertEquals(3, $offer->fresh()->spots);
    }

    public function testWithdrawalThrowsValidationExceptionWhenStudentHasNotApplied(): void
    {
        $student = User::factory()->create();
        $offer = Offer::factory()->create();

        $this->expectException(ValidationException::class);

        $this->action->execute($student, $offer);
    }
}
