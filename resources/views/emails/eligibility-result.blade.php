<x-mail::message>
# Ghana Armed Forces

## Eligibility Result

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

@if ($result->overall_status === 'eligible')
<x-mail::panel>
**Status: ELIGIBLE** ✅

Congratulations! You have met the eligibility requirements for recruitment into the Ghana Armed Forces.
</x-mail::panel>

**Checks Passed:**
@if ($result->age_check) ✅ Age Requirement @endif
@if ($result->nationality_check) ✅ Nationality Requirement @endif
@if ($result->education_check) ✅ Educational Qualification @endif
@if ($result->height_check) ✅ Height Requirement @endif
@if ($result->criminal_check) ✅ Criminal Record Check @endif
@if ($result->document_check) ✅ Document Verification @endif
@if ($result->marital_check) ✅ Marital Status Requirement @endif

Your application will now proceed to the shortlisting process. You will be notified of the outcome in due course.
@else
<x-mail::panel>
**Status: NOT ELIGIBLE** ❌

Unfortunately, you did not meet all the eligibility requirements for this recruitment cycle.
</x-mail::panel>

**Reason(s):**
@if (is_array($result->rejection_reasons) && count($result->rejection_reasons))
@foreach ($result->rejection_reasons as $reason)
- {{ $reason }}
@endforeach
@else
Please review the details of your application for further information.
@endif

We encourage you to review the requirements and consider applying again in future recruitment cycles. Your interest in serving the nation is commendable.
@endif

<x-mail::button :url="config('app.url') . '/applicant/application'" color="primary">
View Full Results
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
