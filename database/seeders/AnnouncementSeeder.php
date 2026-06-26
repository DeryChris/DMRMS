<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'category' => 'general',
                'title' => '2026 Recruitment Exercise Launched',
                'excerpt' => 'The Ghana Armed Forces is pleased to announce the commencement of the 2026 recruitment exercise. Applications are now open.',
                'published_at' => Carbon::parse('2026-06-20'),
            ],
            [
                'category' => 'requirements',
                'title' => 'Updated Document Requirements',
                'excerpt' => 'Please note the updated list of required documents for the application process. All applicants must provide...',
                'published_at' => Carbon::parse('2026-06-18'),
            ],
            [
                'category' => 'deadlines',
                'title' => 'Application Deadline Extended',
                'excerpt' => 'The application deadline has been extended to July 31, 2026 to allow more applicants to complete their submissions.',
                'published_at' => Carbon::parse('2026-06-15'),
            ],
            [
                'category' => 'results',
                'title' => 'Shortlisted Candidates 2025',
                'excerpt' => 'The list of shortlisted candidates for the 2025 recruitment cycle has been published. Check your status on the portal.',
                'published_at' => Carbon::parse('2026-06-10'),
            ],
            [
                'category' => 'general',
                'title' => 'Important: No Middlemen Policy',
                'excerpt' => 'The GAF wishes to remind the public that the recruitment process is free and there are no middlemen. Report any...',
                'published_at' => Carbon::parse('2026-06-05'),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }
    }
}
