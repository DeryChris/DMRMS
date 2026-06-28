<x-mail::message>
# Ghana Armed Forces

## Congratulations! 🎉

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

We are pleased to inform you that you have been **shortlisted** for the next stage of the recruitment process for **{{ $application->cycle->name }}**.

You will receive a separate email with your screening appointment details and verification code.

**What to Bring:**
- Original and photocopies of all educational certificates
- Valid National ID or Passport
- Recent passport-sized photographs (4 copies)
- Medical fitness report (if available)
- Writing materials

<x-mail::button :url="config('app.url') . '/applicant/status'" color="primary">
View Application Status
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
