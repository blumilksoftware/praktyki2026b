<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\DTO\Student\UpdateStudentProfileData;
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
        $student->update([
            "first_name" => $data->firstName,
            "last_name" => $data->lastName,
            "age" => $data->age,
            "street" => $data->street,
            "building_number" => $data->buildingNumber,
            "postal_code" => $data->postalCode,
            "city" => $data->city,
            "university" => $data->university,
            "study_field" => $data->studyField,
            "study_year" => $data->studyYear,
            "specialization" => $data->specialization,
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
