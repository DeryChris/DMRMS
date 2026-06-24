<x-mail::message>
# Ghana Armed Forces

## ⏰ Reminder: Screening Appointment Tomorrow

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

This is a reminder that your screening appointment is scheduled for **tomorrow**.

<x-mail::panel>
### Appointment Summary

**Date:** {{ $appointment->scheduled_date->format('l, j F Y') }}

**Time:** {{ $appointment->scheduled_time }}

**Venue:** {{ $appointment->venue }}

**Slot Number:** {{ $appointment->slot_number ?? 'N/A' }}
</x-mail::panel>

**☐ Pre-Screening Checklist:**
- ☐ Valid National ID or Passport
- ☐ Original educational certificates and transcripts
- ☐ Verification Code (from shortlisting email)
- ☐ Printed appointment notice
- ☐ 4 passport-sized photographs
- ☐ Medical fitness report
- ☐ Writing materials
- ☐ Face mask (if required)

**Important Reminders:**
- Arrive at least **30 minutes early**
- Dress appropriately — no casual wear
- Electronic devices may be restricted at the venue
- Follow all instructions from screening officers

**Contact Information:**
If you encounter any issues or need assistance, please contact the recruitment hotline immediately.

<x-mail::button :url="config('app.url') . '/applicant/screening'" color="primary">
View Appointment Details
</x-mail::button>

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
