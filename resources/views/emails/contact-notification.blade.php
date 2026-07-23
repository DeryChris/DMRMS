<x-mail::message>
# Ghana Armed Forces
## New Contact Message

You have received a new message from the DMRMS contact form.

<x-mail::panel>
**From:** {{ $contactMessage->name }}
**Email:** {{ $contactMessage->email }}
**Subject:** {{ $contactMessage->subject }}
**Sent:** {{ $contactMessage->created_at->format('M d, Y \a\t h:i A') }}
</x-mail::panel>

<x-mail::table>
| Field | Details |
|---|---|
| Name | {{ $contactMessage->name }} |
| Email | {{ $contactMessage->email }} |
| Subject | {{ $contactMessage->subject }} |
| Date | {{ $contactMessage->created_at->format('M d, Y h:i A') }} |
</x-mail::table>

### Message

{{ $contactMessage->message }}

<x-mail::button :url="config('app.url') . '/admin/login'">
Go to Admin Panel
</x-mail::button>

<x-mail::subcopy>
**Reply directly to:** {{ $contactMessage->email }}

This is an automated notification from DMRMS. Please do not reply to this email.

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
