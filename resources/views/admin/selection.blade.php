@extends('layouts.admin')

@section('title', 'Selection - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Selection Committee Review</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left font-medium text-gray-700"><input type="checkbox" class="rounded border-gray-300"></th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Applicant</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">GAF ID</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Eligibility</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Medical</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Fitness</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Interview</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Status</th>
                    <th class="px-6 py-4 text-right font-medium text-gray-700">Decision</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($applications as $app)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-6 py-4 font-medium">{{ $app->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        @if($app->eligibilityResult)
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $app->eligibilityResult->overall_status === 'eligible' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($app->eligibilityResult->overall_status) }}</span>
                        @else
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($app->screeningResult)
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $app->screeningResult->medical_result === 'fit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($app->screeningResult->medical_result) }}</span>
                        @else
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($app->screeningResult)
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $app->screeningResult->fitness_result === 'pass' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($app->screeningResult->fitness_result) }}</span>
                        @else
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($app->screeningResult)
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $app->screeningResult->interview_result === 'recommended' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst(str_replace('_', ' ', $app->screeningResult->interview_result)) }}</span>
                        @else
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['eligibility_passed' => 'bg-green-100 text-green-700', 'shortlisted' => 'bg-amber-50 text-amber-600', 'appointment_scheduled' => 'bg-indigo-50 text-indigo-600', 'screening_completed' => 'bg-emerald-50 text-emerald-600', 'selected' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'][$app->status] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-1">
                        <button class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700">Admit</button>
                        <button class="text-xs bg-yellow-600 text-white px-3 py-1.5 rounded-lg hover:bg-yellow-700">Defer</button>
                        <button class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700">Reject</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No applicants in selection stage</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Batch Selection</h3>
            <div class="flex space-x-3">
                <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">Admit Selected</button>
                <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-medium hover:bg-yellow-700 transition">Defer Selected</button>
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Reject Selected</button>
            </div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Reason / Memo</label><textarea rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></textarea></div>
    </div>
</div>
@endsection
