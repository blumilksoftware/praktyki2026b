<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\DeleteStudyField;
use App\Models\Faculty;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteStudyFieldTest extends TestCase
{
    use RefreshDatabase;

    public function testItDeletesAFieldNobodyUses(): void
    {
        $studyField = StudyField::factory()->create();

        app(DeleteStudyField::class)->execute($studyField);

        $this->assertDatabaseMissing("study_fields", ["id" => $studyField->id]);
    }

    public function testItMovesEnrolledStudentsToTheReplacementField(): void
    {
        $faculty = Faculty::factory()->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        $replacement = StudyField::factory()->for($faculty)->create();
        $student = User::factory()->create(["study_field_id" => $studyField->id]);

        app(DeleteStudyField::class)->execute($studyField, $replacement);

        $this->assertSame($replacement->id, $student->fresh()->study_field_id);
        $this->assertDatabaseMissing("study_fields", ["id" => $studyField->id]);
    }

    public function testItMovesOfferAndPreferenceLinksToTheReplacementField(): void
    {
        $faculty = Faculty::factory()->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        $replacement = StudyField::factory()->for($faculty)->create();

        $offer = Offer::factory()->create();
        $offer->studyFields()->attach($studyField);

        $student = User::factory()->create();
        $student->preferredStudyFields()->attach($studyField);

        app(DeleteStudyField::class)->execute($studyField, $replacement);

        $this->assertTrue($offer->studyFields()->where("study_fields.id", $replacement->id)->exists());
        $this->assertTrue($student->preferredStudyFields()->where("study_fields.id", $replacement->id)->exists());
    }

    public function testItDoesNotDuplicateLinksThatTheReplacementFieldAlreadyHas(): void
    {
        $faculty = Faculty::factory()->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        $replacement = StudyField::factory()->for($faculty)->create();

        $offer = Offer::factory()->create();
        $offer->studyFields()->attach([$studyField->id, $replacement->id]);

        app(DeleteStudyField::class)->execute($studyField, $replacement);

        $this->assertSame(1, $offer->studyFields()->count());
        $this->assertSame($replacement->id, $offer->studyFields()->first()->id);
    }
}
