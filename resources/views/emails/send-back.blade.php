<x-mail::message>
# Ghana Armed Forces

## Application Status Update

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

<x-mail::panel>
**Status: RETURNED FOR RE-REVIEW** 🔄

Your application (**{{ $application->gaf_id }}**) for **{{ $application->cycle->name }}** has been returned from **{{ $fromStatus }}** to **{{ $toStatus }}** for re-review.
</x-mail::panel>

**Reason:**
{{ $reason }}

You will be contacted if any additional information or action is required from your end. Please monitor your portal for further updates.

<x-mail::button :url="config('app.url') . '/applicant/status'" color="primary">
View Application Status
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
