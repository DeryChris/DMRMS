@extends('layouts.admin')

@section('title', 'Application Detail - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Application Detail</h1>
            <p class="text-gray-500 text-sm">{{ $application->gaf_id }} | Submitted {{ $application->submitted_at?->format('F j, Y') ?? $application->created_at->format('F j, Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                <option>Update Status</option>
                <option value="eligibility_passed">Approve</option>
                <option value="rejected">Reject</option>
                <option value="shortlisted">Mark as Shortlisted</option>
                <option value="appointment_scheduled">Schedule Appointment</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Personal Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Name:</span> <span class="font-medium">{{ $application->applicant->name }}</span></div>
                    <div><span class="text-gray-500">DOB:</span> <span class="font-medium">{{ $application->applicant->date_of_birth?->format('Y-m-d') }}</span></div>
                    <div><span class="text-gray-500">Gender:</span> <span class="font-medium">{{ ucfirst($application->applicant->gender) }}</span></div>
                    <div><span class="text-gray-500">Marital:</span> <span class="font-medium">{{ ucfirst($application->applicant->marital_status ?? 'N/A') }}</span></div>
                    <div><span class="text-gray-500">Phone:</span> <span class="font-medium">{{ $application->applicant->contact_number }}</span></div>
                    <div><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $application->applicant->email }}</span></div>
                    <div><span class="text-gray-500">Region:</span> <span class="font-medium">{{ $application->applicant->region }}</span></div>
                    <div><span class="text-gray-500">National ID:</span> <span class="font-medium">{{ $application->applicant->national_id }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Education</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Institution:</span> <span class="font-medium">{{ $application->institution_name ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Qualification:</span> <span class="font-medium">{{ $application->qualification ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Year:</span> <span class="font-medium">{{ $application->year_obtained ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Level:</span> <span class="font-medium">{{ $application->education_level ?? 'N/A' }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Documents</h3>
                <div class="space-y-3">
                    @forelse($application->documents as $doc)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-sm">{{ $doc->document_type }} - {{ $doc->file_name }}</span>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['verified' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'rejected' => 'bg-red-100 text-red-700'][$doc->verification_status] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($doc->verification_status ?? 'pending') }}</span>
                            <a href="{{ $doc->file_url }}" target="_blank" class="text-xs text-gaf-khaki hover:underline font-medium">View</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">No documents uploaded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Eligibility Results</h3>
                @if($application->eligibilityResult)
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Age</span><span class="{{ $application->eligibilityResult->age_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->age_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Nationality</span><span class="{{ $application->eligibilityResult->nationality_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->nationality_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Height</span><span class="{{ $application->eligibilityResult->height_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->height_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Education</span><span class="{{ $application->eligibilityResult->education_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->education_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="mt-3 pt-3 border-t"><span class="font-semibold {{ $application->eligibilityResult->overall_status === 'eligible' ? 'text-green-700' : 'text-red-700' }}">Overall: {{ ucfirst($application->eligibilityResult->overall_status) }}</span></div>
                </div>
                @else
                <p class="text-sm text-gray-500">Not yet evaluated</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Screening Results</h3>
                @if($application->screeningResult)
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Medical</span><span class="font-medium {{ $application->screeningResult->medical_result === 'fit' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($application->screeningResult->medical_result) }}</span></div>
                    <div class="flex justify-between"><span>Fitness</span><span class="font-medium {{ $application->screeningResult->fitness_result === 'pass' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($application->screeningResult->fitness_result) }}</span></div>
                    <div class="flex justify-between"><span>Interview</span><span class="font-medium {{ $application->screeningResult->interview_result === 'recommended' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($application->screeningResult->interview_result) }}</span></div>
                    <div class="mt-3 pt-3 border-t"><span class="font-semibold {{ $application->screeningResult->overall_status === 'pass' ? 'text-green-700' : 'text-red-700' }}">Overall: {{ ucfirst($application->screeningResult->overall_status) }}</span></div>
                </div>
                @else
                <p class="text-sm text-gray-500">Not yet screened</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Appointment</h3>
                @if($application->appointment)
                <div class="text-sm space-y-1">
                    <p><span class="text-gray-500">Date:</span> <span class="font-medium">{{ $application->appointment->scheduled_date?->format('Y-m-d') }}</span></p>
                    <p><span class="text-gray-500">Time:</span> <span class="font-medium">{{ $application->appointment->scheduled_time }}</span></p>
                    <p><span class="text-gray-500">Venue:</span> <span class="font-medium">{{ $application->appointment->venue }}</span></p>
                    <p><span class="text-gray-500">Status:</span> <span class="font-medium">{{ ucfirst($application->appointment->status) }}</span></p>
                </div>
                @else
                <p class="text-sm text-gray-500">Not scheduled</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Final Decision</h3>
                @if($application->finalDecision)
                <div class="text-sm space-y-1">
                    <p><span class="text-gray-500">Decision:</span> <span class="font-medium {{ $application->finalDecision->decision === 'selected' ? 'text-green-700' : 'text-red-700' }}">{{ ucfirst($application->finalDecision->decision) }}</span></p>
                    <p><span class="text-gray-500">Reason:</span> <span class="font-medium">{{ $application->finalDecision->decision_reason ?? 'N/A' }}</span></p>
                    <p><span class="text-gray-500">Date:</span> <span class="font-medium">{{ $application->finalDecision->decision_date?->format('Y-m-d') }}</span></p>
                </div>
                @else
                <p class="text-sm text-gray-500">No decision yet</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Actions</h3>
                <div class="space-y-2">
                    <button class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">Approve</button>
                    <button class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Reject</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Audit Log</h3>
                <div class="space-y-2 text-xs text-gray-500">
                    @forelse($auditLogs as $log)
                    <p><span class="text-gray-700">{{ $log->user_type ?? 'System' }}</span> {{ $log->action }} - {{ $log->created_at->diffForHumans() }}</p>
                    @empty
                    <p class="text-gray-500">No audit logs yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
