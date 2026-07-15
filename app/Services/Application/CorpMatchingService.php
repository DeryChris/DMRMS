<?php

namespace App\Services\Application;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Corp;
use App\Models\Sector;
use App\Models\EducationLevel;
use App\Models\CorpEducationRequirement;
use Illuminate\Support\Collection;

class CorpMatchingService
{
    const ANY = 'any';
    const STEM = 'stem';
    const ENGINEERING = 'engineering';
    const BUSINESS = 'business';
    const ARTS = 'arts';
    const EDUCATION = 'education';
    const MEDICAL = 'medical';
    const SPECIFIC = 'specific';

    const FIELD_GROUPS = [
        'stem' => [
            'Computer Science', 'Mathematics', 'Physics', 'Chemistry', 'Biology',
            'Statistics', 'Information Technology', 'Biochemistry', 'Microbiology',
            'Environmental Science', 'Geology', 'Geography', 'Materials Science',
        ],
        'engineering' => [
            'Civil Engineering', 'Mechanical Engineering', 'Electrical Engineering',
            'Electronic Engineering', 'Computer Engineering', 'Telecom Engineering',
            'Chemical Engineering', 'Aerospace Engineering', 'Marine Engineering',
            'Geomatic Engineering', 'Agricultural Engineering', 'Instrumentation Engineering',
            'Software Engineering', 'Systems Engineering', 'Industrial Engineering',
            'Avionics', 'Aeronautical Engineering', 'Biomedical Engineering',
            'Construction Technology', 'Quantity Surveying', 'Architecture',
            'Naval Architecture', 'Meteorology', 'Aviation Technology',
            'Land Economy', 'Landscape Designing',
        ],
        'business' => [
            'Accounting', 'Finance', 'Banking', 'Business Administration',
            'Marketing', 'Human Resource Management', 'Management Studies',
            'Economics', 'Logistics', 'Supply Chain Management', 'Purchasing and Supply',
            'Ports and Shipping Administration', 'Procurement', 'Secretarial Studies',
            'Business Studies', 'Actuarial Science',
        ],
        'arts' => [
            'Political Science', 'Psychology', 'Sociology', 'History',
            'English', 'French', 'Arabic', 'Chinese', 'German',
            'Philosophy', 'Religious Studies', 'Social Work', 'Anthropology',
            'Linguistics', 'Communication Studies', 'Journalism', 'Media Studies',
            'Graphic Design', 'Film Production', 'Music', 'Theatre Arts',
            'Fine Arts', 'Defence Studies', 'Security Studies',
        ],
        'education' => [
            'Education', 'Arts Education', 'Science Education', 'Mathematics Education',
            'Social Studies Education', 'Early Childhood Education', 'Special Education',
            'Physical Education', 'French Education', 'English Education',
        ],
        'medical' => [
            'Medicine', 'Surgery', 'Dentistry', 'Pharmacy', 'Nursing',
            'Midwifery', 'Laboratory Science', 'Radiography', 'Physiotherapy',
            'Dietetics', 'Nutrition', 'Optometry', 'Biomedical Science',
            'Audiology', 'Speech Therapy', 'Public Health', 'Environmental Health',
            'Health Education', 'Veterinary Medicine', 'Psychology (Clinical)',
            'Anaesthesia', 'Health Information Systems', 'Biostatistics',
            'Prosthetics and Orthotics',
        ],
    ];

    public static function getAllDegreeFields(): array
    {
        $specificDegrees = CorpEducationRequirement::whereNotNull('specific_degrees')
            ->pluck('specific_degrees')
            ->map(fn($json) => is_array($json) ? $json : json_decode($json, true) ?? [])
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $groupFields = collect(self::FIELD_GROUPS)->flatten()->unique()->toArray();

        return array_values(array_unique(array_merge($groupFields, $specificDegrees)));
    }

    public function getDegreeFieldGroup(string $field): ?string
    {
        foreach (self::FIELD_GROUPS as $group => $fields) {
            if (in_array($field, $fields)) {
                return $group;
            }
        }
        return null;
    }

    public function getEligibleSectors(Application $application): Collection
    {
        $applicant = $application->applicant;
        $educationLevel = $this->resolveEducationLevel($application->education_level);
        $degreeField = $application->degree_field;

        if (!$educationLevel) {
            return collect();
        }

        $corpIds = $this->getMatchingCorpIds($educationLevel->id, $degreeField);

        $sectorIds = Corp::whereIn('id', $corpIds)
            ->where('is_active', true)
            ->pluck('sector_id')
            ->unique();

        return Sector::whereIn('id', $sectorIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getEligibleCorps(int $sectorId, Application $application): Collection
    {
        $educationLevel = $this->resolveEducationLevel($application->education_level);
        $degreeField = $application->degree_field;

        if (!$educationLevel) {
            return collect();
        }

        $corpIds = $this->getMatchingCorpIds($educationLevel->id, $degreeField);

        return Corp::whereIn('id', $corpIds)
            ->where('sector_id', $sectorId)
            ->where('is_active', true)
            ->with('sector')
            ->orderBy('name')
            ->get();
    }

    public function getEligibleServices(int $sectorId, Application $application): Collection
    {
        return $this->getEligibleCorps($sectorId, $application)
            ->pluck('service')
            ->unique()
            ->values();
    }

    public function isEligible(Application $application, Corp $corp): bool
    {
        $educationLevel = $this->resolveEducationLevel($application->education_level);
        $degreeField = $application->degree_field;

        if (!$educationLevel) {
            return false;
        }

        $matchingIds = $this->getMatchingCorpIds($educationLevel->id, $degreeField);

        return in_array($corp->id, $matchingIds);
    }

    private function resolveEducationLevel(?string $level): ?EducationLevel
    {
        $map = [
            'ssce' => 'wasce',
            'diploma' => 'diploma-hnd',
            'degree' => 'degree',
            'masters' => 'masters',
            'phd' => 'doctorate',
        ];

        $slug = $map[$level] ?? null;
        if (!$slug) {
            return null;
        }

        return EducationLevel::where('slug', $slug)->first();
    }

    private function getMatchingCorpIds(int $educationLevelId, ?string $degreeField): array
    {
        $requirements = CorpEducationRequirement::where('education_level_id', $educationLevelId)
            ->with('corp')
            ->get();

        $matchingIds = [];

        foreach ($requirements as $req) {
            $pass = false;

            switch ($req->degree_field_group) {
                case self::ANY:
                    $pass = true;
                    break;
                case self::SPECIFIC:
                    $specificDegrees = $req->specific_degrees ?? [];
                    $pass = in_array($degreeField, $specificDegrees);
                    break;
                default:
                    $fields = self::FIELD_GROUPS[$req->degree_field_group] ?? [];
                    $pass = in_array($degreeField, $fields);
                    break;
            }

            if ($pass) {
                $matchingIds[] = $req->corp_id;
            }
        }

        return array_unique($matchingIds);
    }
}
