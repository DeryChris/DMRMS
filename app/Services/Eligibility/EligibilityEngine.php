<?php

namespace App\Services\Eligibility;

use App\Models\Application;
use Carbon\Carbon;

class EligibilityEngine
{
    public function evaluate(Application $app): array
    {
        $cycle = $app->cycle;
        $requirements = $cycle->requirements ?? [];
        $applicant = $app->applicant;

        $checks = [];

        $ageCheck = $this->checkAge($applicant->date_of_birth, $requirements);
        $nationalityCheck = $this->checkNationality($applicant->nationality);
        $educationCheck = $this->checkEducation($app->education_level, $requirements);
        $heightCheck = $this->checkHeight($app->height, $applicant->gender, $requirements);
        $maritalCheck = $this->checkMaritalStatus($applicant->marital_status, $requirements);
        $criminalCheck = $this->checkCriminalRecord($app->criminal_record);
        $documentCheck = $this->checkDocuments($app);

        $checks = [
            'age'        => ['passed' => $ageCheck, 'criterion' => 'Age requirement'],
            'nationality' => ['passed' => $nationalityCheck, 'criterion' => 'Nationality requirement'],
            'education'   => ['passed' => $educationCheck, 'criterion' => 'Education requirement'],
            'height'      => ['passed' => $heightCheck, 'criterion' => 'Height requirement'],
            'marital_status' => ['passed' => $maritalCheck, 'criterion' => 'Marital status requirement'],
            'criminal_record' => ['passed' => $criminalCheck, 'criterion' => 'Criminal record check'],
            'documents'   => ['passed' => $documentCheck, 'criterion' => 'Document verification'],
        ];

        $failed = array_filter($checks, fn($c) => !$c['passed']);
        $overallStatus = empty($failed) ? 'eligible' : 'rejected';
        $rejectionReasons = [];

        if (!empty($failed)) {
            foreach ($failed as $key => $check) {
                $rejectionReasons[] = "Failed {$key}: {$check['criterion']}";
            }
        }

        return [
            'overall_status'    => $overallStatus,
            'checks'            => $checks,
            'rejection_reasons' => $rejectionReasons,
        ];
    }

    public function checkAge($dob, array $cycleRequirements = []): bool
    {
        $age = Carbon::parse($dob)->age;

        $minAge = $cycleRequirements['age_min'] ?? config('recruitment.age_min', 18);
        $maxAge = $cycleRequirements['age_max'] ?? config('recruitment.age_max_regular', 25);

        if (isset($cycleRequirements['category'])) {
            $category = $cycleRequirements['category'];
            $key = "age_max_{$category}";
            $maxAge = $cycleRequirements[$key] ?? config("recruitment.{$key}", $maxAge);
        }

        return $age >= $minAge && $age <= $maxAge;
    }

    public function checkNationality($nationality): bool
    {
        $required = config('recruitment.nationality', 'Ghanaian');

        return strcasecmp($nationality, $required) === 0;
    }

    public function checkEducation($educationLevel, array $cycleRequirements = []): bool
    {
        $allowed = $cycleRequirements['education_levels'] ?? [
            'WASSCE', 'SSSCE', 'GCE Advanced Level', 'Diploma', 'Degree',
        ];

        return in_array(strtoupper($educationLevel), array_map('strtoupper', $allowed));
    }

    public function checkHeight($height, $gender, array $cycleRequirements = []): bool
    {
        $minHeight = $cycleRequirements['height_min'] ?? (
            strtolower($gender) === 'male'
                ? config('recruitment.height_min_male', 1.65)
                : config('recruitment.height_min_female', 1.58)
        );

        return $height >= $minHeight;
    }

    public function checkMaritalStatus($status, array $cycleRequirements = []): bool
    {
        $allowed = $cycleRequirements['marital_statuses'] ?? ['Single', 'Never Married'];

        return in_array(ucfirst(strtolower($status)), $allowed);
    }

    public function checkCriminalRecord($hasRecord): bool
    {
        return !$hasRecord;
    }

    public function checkDocuments(Application $application): bool
    {
        $requiredDocs = ['birth_certificate', 'educational_certificate', 'national_id', 'passport_photograph'];

        $uploaded = $application->documents()
            ->whereIn('document_type', $requiredDocs)
            ->count();

        return $uploaded === count($requiredDocs);
    }
}
