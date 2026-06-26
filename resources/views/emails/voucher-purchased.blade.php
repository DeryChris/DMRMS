<x-mail::message>
# Ghana Armed Forces

## Your Recruitment Voucher

Dear **{{ $voucher->purchaser_name }}**,

Thank you for purchasing a recruitment voucher. Please use the credentials below to create your account and begin your application.

<x-mail::panel>
**Serial Number:** {{ $voucher->serial_number }}

**PIN Code:** {{ $voucher->pin_code }}
</x-mail::panel>

<x-mail::table>
| Detail | Info |
|---|---|
| Recruitment Cycle | {{ $voucher->cycle->name }} |
| Amount Paid | GHS {{ number_format($voucher->cost, 2) }} |
| Valid Until | {{ $voucher->expires_at?->format('M d, Y H:i') }} |
</x-mail::table>

<x-mail::button :url="config('app.url') . '/applicant/register?serial=' . $voucher->serial_number . '&pin=' . $voucher->pin_code" color="primary">
Proceed to Register
</x-mail::button>

<x-mail::subcopy>
**Important:** Keep your serial number and PIN code confidential. Do not share them with anyone. Each voucher can only be used once.

This is an automated message from DMRMS. Do not reply to this email.

For assistance, contact: recruitment@gaf.mil.gh | Tel: +233 (0) 302 123 456

Ghana Armed Forces – Defence Manpower Recruitment Management System
</x-mail::subcopy>
</x-mail::message>
