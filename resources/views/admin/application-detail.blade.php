@extends('layouts.admin')

@section('title', 'Application Detail - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Application Detail</h1>
            <p class="text-gray-500 text-sm">{{ $application->gaf_id }} | Submitted {{ $application->submitted_at?->format('F j, Y') ?? $application->created_at->format('F j, Y') }}</p>
        </div>
        <div>
            {!! status_badge($application->status) !!}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-strong rounded-xl shadow-sm p-6">
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

            <div class="glass-strong rounded-xl shadow-sm p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Education</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Institution:</span> <span class="font-medium">{{ $application->institution_name ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Qualification:</span> <span class="font-medium">{{ $application->qualification ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Year:</span> <span class="font-medium">{{ $application->year_obtained ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Level:</span> <span class="font-medium">{{ $application->education_level ?? 'N/A' }}</span></div>
                </div>
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Documents</h3>
                <div class="space-y-3">
                    @forelse($application->documents as $doc)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 gradient-border-left pl-3">
                        <div>
                            <span class="text-sm font-medium">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $doc->file_name }}</span>
                            {!! status_badge($doc->verification_status ?? 'pending', 'document') !!}
                        </div>
                        <div class="flex items-center space-x-2">
                            <x-document-viewer :document="$doc" :admin="true" :documents="$application->documents" />
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">No documents uploaded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-heading font-semibold text-lg text-gray-800">Eligibility Results</h3>
                    <form action="{{ route('admin.applications.refresh-eligibility', $application->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors" title="Re-run eligibility check">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh
                        </button>
                    </form>
                </div>
                @if($application->eligibilityResult)
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Age</span><span class="{{ $application->eligibilityResult->age_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->age_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Nationality</span><span class="{{ $application->eligibilityResult->nationality_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->nationality_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Education</span><span class="{{ $application->eligibilityResult->education_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->education_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Height</span><span class="{{ $application->eligibilityResult->height_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->height_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Marital Status</span><span class="{{ $application->eligibilityResult->marital_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->marital_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Criminal Record</span><span class="{{ $application->eligibilityResult->criminal_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->criminal_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="flex justify-between"><span>Documents</span><span class="{{ $application->eligibilityResult->document_check ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $application->eligibilityResult->document_check ? 'Pass' : 'Fail' }}</span></div>
                    <div class="mt-3 pt-3 border-t"><span class="font-semibold {{ $application->eligibilityResult->overall_status === 'eligible' ? 'text-green-700' : 'text-red-700' }}">Overall: {{ ucfirst($application->eligibilityResult->overall_status) }}</span></div>
                    @if(!empty($application->eligibilityResult->rejection_reasons))
                    <div class="mt-2 pt-2 border-t text-xs text-red-600">
                        @foreach((array) $application->eligibilityResult->rejection_reasons as $reason)
                        <p>&bull; {{ $reason }}</p>
                        @endforeach
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-gray-500">Not yet evaluated</p>
                @endif
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
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

            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
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

            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Final Decision</h3>
                @if($application->finalDecision)
                <div class="text-sm space-y-1">
                    <p><span class="text-gray-500">Decision:</span> <span class="font-medium {{ in_array($application->finalDecision->decision, ['admitted', 'selected']) ? 'text-green-700' : ($application->finalDecision->decision === 'reserve' ? 'text-blue-700' : 'text-red-700') }}">{{ ucfirst($application->finalDecision->decision) }}</span></p>
                    <p><span class="text-gray-500">Reason:</span> <span class="font-medium">{{ $application->finalDecision->decision_reason ?? 'N/A' }}</span></p>
                    <p><span class="text-gray-500">Date:</span> <span class="font-medium">{{ $application->finalDecision->decision_date?->format('Y-m-d') }}</span></p>
                    @if($application->finalDecision->committee_approved_at)
                    <p><span class="text-gray-500">Committee:</span> <span class="font-medium text-green-600">Approved {{ $application->finalDecision->committee_approved_at->format('Y-m-d H:i') }}</span></p>
                    @endif
                    @if($application->finalDecision->evaluation)
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <p class="text-gray-500 text-xs font-medium mb-1">Evaluation Scores</p>
                        <div class="grid grid-cols-5 gap-2 text-xs">
                            @foreach($application->finalDecision->evaluation as $key => $val)
                            <div><span class="text-gray-400">{{ ucfirst($key) }}:</span> <span class="font-semibold">{{ $val }}</span></div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-gray-500">No decision yet</p>
                @endif

                @if(in_array($application->status, ['selected', 'recruited']))
                <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
                    <a href="{{ route('admin.offer-letter', $application->id) }}" class="block w-full text-center px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">
                        Download Offer Letter
                    </a>
                </div>
                @endif

                @if($application->status === 'selected')
                <div class="mt-2">
                    <form method="POST" action="{{ route('admin.recruit', $application->id) }}" class="space-y-2" onsubmit="return confirm('Mark this applicant as recruited? This confirms enrollment.')">
                        @csrf
                        <input type="date" name="enrollment_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs" value="{{ now()->format('Y-m-d') }}">
                        <input type="text" name="training_battalion" placeholder="Training battalion (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                        <button type="submit" class="block w-full text-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                            Mark as Recruited
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Recruitment Journey</h3>
                @php
                    $stages = config('recruitment.application_stages');
                    $currentStatus = $application->status;
                    $currentIndex = array_search($currentStatus, $stages);
                    if ($currentIndex === false) $currentIndex = -1;
                @endphp
                <div class="space-y-1">
                    @foreach($stages as $i => $stage)
                    @php
                        $isCompleted = $i < $currentIndex;
                        $isCurrent = $i === $currentIndex;
                        $isPending = $i > $currentIndex;
                        $stageLabel = config("recruitment.statuses.{$stage}.label", ucfirst(str_replace('_', ' ', $stage)));
                        $stageColor = $isCompleted ? 'bg-green-500' : ($isCurrent ? 'bg-gaf-green' : 'bg-gray-200');
                        $textColor = $isCompleted ? 'text-green-700' : ($isCurrent ? 'text-gaf-dark-green font-semibold' : 'text-gray-400');
                    @endphp
                    <div class="flex items-center space-x-3 py-1.5">
                        <div class="w-3 h-3 rounded-full flex-shrink-0 {{ $stageColor }}"></div>
                        <span class="text-xs {{ $textColor }}">{{ $stageLabel }}</span>
                        @if($isCurrent && $application->updated_at)
                        <span class="text-xs text-gray-400 ml-auto">{{ $application->updated_at->format('M d, H:i') }}</span>
                        @endif
                        @if($isCompleted && $i === $currentIndex - 1 && $application->submitted_at && $stage === 'submitted')
                        <span class="text-xs text-gray-400 ml-auto">{{ $application->submitted_at->format('M d, H:i') }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6">
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
