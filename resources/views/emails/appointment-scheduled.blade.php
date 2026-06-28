<x-mail::message>
# Ghana Armed Forces

## Appointment Scheduled

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

Your screening appointment has been scheduled. Please find the details below.

<x-mail::panel>
### 🗓 Appointment Details

**Date:** {{ $appointment->scheduled_date->format('l, j F Y') }}

**Time:** {{ $appointment->scheduled_time }}

**Venue:** {{ $appointment->venue }}

**Slot Number:** {{ $appointment->slot_number ?? 'N/A' }}
</x-mail::panel>

@if($code)
<x-mail::panel>
### ✅ Your Verification Code

**Present this code at the screening centre.**

# **{{ $code }}**

@if($qrPath)
![QR Code]({{ asset($qrPath) }})
@endif
</x-mail::panel>
@endif

**Items to Bring:**
- Printed copy of this appointment notice
- Valid National ID or Passport
- Original educational certificates and transcripts
- Your verification code (above)
- Medical fitness report
- 4 recent passport-sized photographs
- Writing materials (pen, pencil, eraser)

**Please arrive at least 30 minutes before your scheduled time.**

<x-mail::button :url="config('app.url') . '/applicant/appointment'" color="primary">
View Appointment Card
</x-mail::button>

**Need to Reschedule?**
If you are unable to attend on the scheduled date, please log in to your portal at least 48 hours before the appointment to request a reschedule. Failure to attend without prior notice may result in disqualification.

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
