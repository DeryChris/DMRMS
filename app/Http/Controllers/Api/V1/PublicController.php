<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function activeCycles(): JsonResponse
    {
        $cycles = Cycle::where('status', 'active')
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'cycle_code', 'start_date', 'end_date', 'application_deadline', 'total_vacancies', 'requirements']);

        return response()->json(['data' => $cycles]);
    }

    public function cycleRequirements($id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);

        return response()->json([
            'data' => [
                'id'           => $cycle->id,
                'name'         => $cycle->name,
                'requirements' => $cycle->requirements,
            ],
        ]);
    }

    public function announcements(): JsonResponse
    {
        $announcements = [
            [
                'id'          => 1,
                'title'       => '2026 Recruitment Cycle Now Open',
                'description' => 'The 2026 military recruitment cycle is now accepting applications through DMRMS.',
                'published_at'=> '2026-01-15T08:00:00Z',
            ],
            [
                'id'          => 2,
                'title'       => 'New AI-Powered Screening Process',
                'description' => 'The DMRMS now features AI-assisted document verification and candidate ranking.',
                'published_at'=> '2026-03-01T10:00:00Z',
            ],
            [
                'id'          => 3,
                'title'       => 'Deadline Extension for Northern Region',
                'description' => 'Application deadline extended for applicants from the northern region.',
                'published_at'=> '2026-04-10T14:00:00Z',
            ],
        ];

        return response()->json([
            'data' => $announcements,
            'meta' => [
                'current_page' => 1,
                'per_page'     => 15,
                'total'        => count($announcements),
            ],
        ]);
    }

    public function preEligibilityCheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'age'         => 'required|integer|min:18|max:35',
            'nationality' => 'required|string',
            'education'   => 'required|string',
            'height'      => 'nullable|numeric',
            'weight'      => 'nullable|numeric',
        ]);

        $passed = true;
        $reasons = [];

        $age = $validated['age'];
        if ($age < 18 || $age > 26) {
            $passed = false;
            $reasons[] = 'Age must be between 18 and 26 years.';
        }

        if (strtolower($validated['nationality']) !== 'ghanaian') {
            $passed = false;
            $reasons[] = 'Must be a Ghanaian citizen.';
        }

        $validEducations = ['ssce', 'wassce', 'degree', 'hnd', 'diploma', 'advanced'];
        if (!in_array(strtolower($validated['education']), $validEducations)) {
            $passed = false;
            $reasons[] = 'Minimum education requirement: SSCE/WASSCE or higher.';
        }

        return response()->json([
            'eligible' => $passed,
            'reasons'  => $reasons,
            'data'     => $validated,
        ]);
    }

    public function faqs(): JsonResponse
    {
        $faqs = [
            [
                'question' => 'What is DMRMS?',
                'answer'   => 'The Defence Manpower Recruitment Management System is a digital platform for military recruitment.',
            ],
            [
                'question' => 'How do I obtain a voucher?',
                'answer'   => 'Vouchers can be purchased at designated banks or recruitment centers nationwide.',
            ],
            [
                'question' => 'What documents are required?',
                'answer'   => 'Birth certificate, national ID, educational certificates, and passport photographs.',
            ],
            [
                'question' => 'Can I apply for multiple cycles?',
                'answer'   => 'No, you can only apply for one active recruitment cycle at a time.',
            ],
            [
                'question' => 'How long does the verification process take?',
                'answer'   => 'Email and phone verification codes are typically delivered within 5 minutes.',
            ],
        ];

        return response()->json(['data' => $faqs]);
    }
}
