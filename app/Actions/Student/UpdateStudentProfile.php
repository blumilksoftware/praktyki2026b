<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\DTO\Student\UpdateStudentProfileData;
use App\Models\University;
use App\Models\User;
use App\Services\MapboxGeocodingService;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UpdateStudentProfile
{
    public function __construct(
        private readonly MapboxGeocodingService $geocodingService,
    ) {}

    public function execute(User $student, UpdateStudentProfileData $data): User
    {
        $university = $data->universityId !== null ? University::find($data->universityId) : null;

        $student->update([
            "first_name" => $data->firstName,
            "last_name" => $data->lastName,
            "street" => $data->street,
            "postal_code" => $data->postalCode,
            "city" => $data->city,
            "university" => $university->name ?? $data->university,
            "organization_id" => $university?->id,
            "study_field_id" => $data->studyFieldId,
            "study_year" => $data->studyYear,
            "specialization" => $data->specialization,
            "skills" => $data->skills,
            "work_modes" => $data->workModes,
        ]);

        $student->preferredStudyFields()->sync($data->studyFieldIds);
        $this->syncPreferredCities($student, $data->preferredCities);

        return $student->fresh();
    }

    private function syncPreferredCities(User $student, array $cities): void
    {
        $existingCities = $student->preferredCities()->pluck("city")->all();
        $removedCities = array_diff($existingCities, $cities);
        $newCities = array_diff($cities, $existingCities);

        if ($removedCities !== []) {
            $student->preferredCities()->whereIn("city", $removedCities)->delete();
        }

        foreach ($newCities as $city) {
            try {
                $coordinates = $this->geocodingService->geocode($city);
            } catch (RuntimeException) {
                throw ValidationException::withMessages([
                    "preferred_cities" => __("validation.city_geocoding_failed"),
                ]);
            }

            $student->preferredCities()->create([
                "city" => $city,
                "latitude" => $coordinates["latitude"],
                "longitude" => $coordinates["longitude"],
            ]);
        }
    }
}
