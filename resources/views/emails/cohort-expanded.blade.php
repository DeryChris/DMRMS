<x-mail::message>
# Ghana Armed Forces

## Cohort Expansion Notice

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

Additional candidates have been selected from the reserve list to fill remaining vacancies in the **{{ $cycleName }}** cycle.

<x-mail::panel>
Your cohort has been expanded to **{{ $newTotal }}** candidates.

This expansion strengthens our intake and reflects the high quality of applicants in this recruitment cycle.
</x-mail::panel>

All previously communicated appointments and instructions remain unchanged. You are expected to proceed with your training as scheduled.

<x-mail::button :url="config('app.url') . '/applicant/status'" color="primary">
View Application Status
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
