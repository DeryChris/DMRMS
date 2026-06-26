<x-mail::message>
# Ghana Armed Forces

## Final Decision Pending

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

<x-mail::panel>
**Status: SCREENING COMPLETE — AWAITING FINAL DECISION**

Your screening process has been completed successfully for application **{{ $application->gaf_id }}**.
</x-mail::panel>

Your application is now pending final review by the selection committee. You will be notified of the final decision once it has been made.

We appreciate your patience throughout this process.

<x-mail::button :url="config('app.url') . '/applicant/status'" color="primary">
View Application Status
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
