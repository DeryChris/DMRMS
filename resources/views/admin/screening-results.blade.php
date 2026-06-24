@extends('layouts.admin')

@section('title', 'Screening Results - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Screening Results</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr><th class="px-6 py-4 text-left font-medium text-gray-700">Applicant</th><th class="px-6 py-4 text-left font-medium text-gray-700">GAF ID</th><th class="px-6 py-4 text-left font-medium text-gray-700">Medical</th><th class="px-6 py-4 text-left font-medium text-gray-700">Fitness</th><th class="px-6 py-4 text-left font-medium text-gray-700">Interview</th><th class="px-6 py-4 text-left font-medium text-gray-700">Overall</th><th class="px-6 py-4 text-right font-medium text-gray-700">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($results as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $r->application->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $r->application->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['fit' => 'bg-green-100 text-green-700', 'unfit' => 'bg-red-100 text-red-700', 'pending' => 'bg-yellow-100 text-yellow-700'][$r->medical_result] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($r->medical_result ?? 'Pending') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['pass' => 'bg-green-100 text-green-700', 'fail' => 'bg-red-100 text-red-700', 'pending' => 'bg-yellow-100 text-yellow-700'][$r->fitness_result] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($r->fitness_result ?? 'Pending') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['recommended' => 'bg-green-100 text-green-700', 'not_recommended' => 'bg-red-100 text-red-700', 'pending' => 'bg-yellow-100 text-yellow-700'][$r->interview_result] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst(str_replace('_', ' ', $r->interview_result ?? 'Pending')) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['pass' => 'bg-green-100 text-green-700', 'fail' => 'bg-red-100 text-red-700', 'in_progress' => 'bg-yellow-100 text-yellow-700'][$r->overall_status] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst(str_replace('_', ' ', $r->overall_status ?? 'Pending')) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700">Pass</button>
                        <button class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700">Fail</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No screening results yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Quick Entry Form</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Medical Result</label><select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"><option>Fit</option><option>Unfit</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Fitness Result</label><select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"><option>Pass</option><option>Fail</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Interview Result</label><select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"><option>Recommended</option><option>Not Recommended</option></select></div>
        </div>
        <div class="mt-4"><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></textarea></div>
        <button class="mt-4 px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Results</button>
    </div>
</div>
@endsection
