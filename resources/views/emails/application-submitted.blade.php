<x-mail::message>
# Ghana Armed Forces

## Application Submitted

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

Your application has been received successfully.

<x-mail::panel>
**GAF ID:** {{ $application->gaf_id }}

**Recruitment Cycle:** {{ $application->cycle->name }}

**Date Submitted:** {{ $application->submitted_at->format('j F Y, g:i a') }}

**Status:** Pending Review
</x-mail::panel>

**What Happens Next:**
- Your application will undergo eligibility screening
- You will be notified of the eligibility result via email
- If eligible, you will proceed to the shortlisting stage
- Shortlisted candidates will receive appointment details for screening

**Estimated Timeline:**
Eligibility results are typically available within **5–7 working days** after submission.

<x-mail::button :url="config('app.url') . '/applicant/application'" color="primary">
View Application Status
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
