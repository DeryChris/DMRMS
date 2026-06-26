<x-mail::message>
# Ghana Armed Forces

## Verify Your Email Address

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

Thank you for registering with the Defence Manpower Recruitment Management System (DMRMS). Please use the verification code below to confirm your email address.

<x-mail::panel>
**Your Verification Code:** **{{ $code }}**
</x-mail::panel>

This code will expire in 30 minutes. If you did not create an account, please ignore this email.

<x-mail::button :url="config('app.url') . '/applicant/verify-email'" color="primary">
Verify Email Address
</x-mail::button>

---

**Important:** Never share your verification code with anyone. Official communication from the Ghana Armed Forces will never ask for your password or verification code.

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
