<x-mail::message>
# Ghana Armed Forces

## Account Activated

Dear **{{ $applicant->first_name }} {{ $applicant->last_name }}**,

Your account has been successfully activated. You can now log in to the Defence Manpower Recruitment Management System (DMRMS) to complete your application.

<x-mail::button :url="config('app.url') . '/login'" color="primary">
Login to Your Account
</x-mail::button>

**Next Steps:**
1. Log in using your credentials
2. Complete your profile information
3. Apply for the current recruitment cycle
4. Upload required documents

If you did not create this account, please contact the recruitment board immediately.

---

**Important:** Never share your login credentials with anyone. Official communication from the Ghana Armed Forces will never ask for your password.

<x-mail::subcopy>
This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
