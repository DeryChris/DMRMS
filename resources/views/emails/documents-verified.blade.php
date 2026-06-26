<x-mail::message>
# Ghana Armed Forces

## Documents Verified

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

<x-mail::panel>
**Status: DOCUMENTS VERIFIED** ✅

All your required documents have been reviewed and verified successfully for application **{{ $application->gaf_id }}**.
</x-mail::panel>

Your application will now proceed to the eligibility evaluation stage. You will be notified of the results shortly.

<x-mail::button :url="config('app.url') . '/applicant/status'" color="primary">
View Application Status
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
