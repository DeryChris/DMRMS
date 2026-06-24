<x-mail::message>
# Ghana Armed Forces

## Recruitment Decision

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

Regarding your application (GAF ID: **{{ $application->gaf_id }}**) for **{{ $application->cycle->name }}**:

@if ($decision->decision === 'admitted' || $decision->decision === 'approved')
<x-mail::panel>
### Decision: ADMITTED ✅

**Welcome to the Ghana Armed Forces!**

We are pleased to inform you that your application has been successful. You have been admitted into the Ghana Armed Forces.
</x-mail::panel>

**Next Steps:**
1. Download your admission letter below
2. Report to the training centre on the specified date
3. Bring all original documents for verification
4. Prepare for basic military training

<x-mail::button :url="config('app.url') . '/applicant/admission-letter'" color="primary">
Download Admission Letter
</x-mail::button>

Further details regarding training commencement will be communicated in due course.

@elseif ($decision->decision === 'deferred' || $decision->decision === 'pending')
<x-mail::panel>
### Decision: DEFERRED ⏳

Your application has been deferred for further consideration.
</x-mail::panel>

**Reason:**
{{ $decision->decision_reason ?? 'Additional review is required for your application.' }}

Your application will be reconsidered in the next evaluation phase. You may be contacted for additional information or assessments.

@else
<x-mail::panel>
### Decision: NOT ADMITTED ❌

We regret to inform you that your application has not been successful in this recruitment cycle.
</x-mail::panel>

**Reason:**
{{ $decision->decision_reason ?? 'Your application did not meet the required standards for this cycle.' }}

We appreciate your interest in serving the nation. You are encouraged to apply again in future recruitment cycles.
@endif

@if ($decision->decision !== 'admitted' && $decision->decision !== 'approved')
**Appeal Process:**
If you believe there has been an error or have additional information to support your application, you may submit an appeal within **14 days** of this notification. Please log in to your portal and navigate to the Appeals section.

<x-mail::button :url="config('app.url') . '/applicant/appeal'" color="primary">
Submit an Appeal
</x-mail::button>
@endif

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
