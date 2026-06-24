<x-mail::message>
# Ghana Armed Forces

## Congratulations! 🎉

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

We are pleased to inform you that you have been **shortlisted** for the next stage of the recruitment process for **{{ $application->cycle->name }}**.

<x-mail::panel>
**Your Verification Code:**

# **{{ $verificationCode }}**

*Please present this code at the screening centre.*
</x-mail::panel>

**What to Bring:**
- Printed copy of this email
- Original and photocopies of all educational certificates
- Valid National ID or Passport
- Recent passport-sized photographs (4 copies)
- Medical fitness report (if available)
- Writing materials

@if ($application->appointment)
**Screening Details:**
- **Date:** {{ $application->appointment->scheduled_date->format('l, j F Y') }}
- **Time:** {{ $application->appointment->scheduled_time }}
- **Venue:** {{ $application->appointment->venue }}
@endif

<x-mail::button :url="config('app.url') . '/applicant/screening'" color="primary">
View Screening Details
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
