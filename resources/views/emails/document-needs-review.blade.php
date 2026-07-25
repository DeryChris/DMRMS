<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Document Requires Manual Review' }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                    {{-- Header --}}
                    <tr>
                        <td style="background: #1e40af; padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px;">Ghana Armed Forces</h1>
                            <p style="color: #93c5fd; margin: 8px 0 0 0; font-size: 14px;">Document Requires Manual Review</p>
                        </td>
                    </tr>
                    {{-- Body --}}
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333; font-size: 15px; line-height: 1.6;">Dear <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>,</p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background: #eff6ff; border-left: 4px solid #2563eb; border-radius: 8px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="color: #1e40af; font-size: 16px; margin: 0; font-weight: bold;">📋 {{ $documentType }} — Pending Manual Review</p>
                                        <p style="color: #1e40af; font-size: 14px; margin: 8px 0 0 0;">
                                            The system was unable to auto-verify your <strong>{{ $documentType }}</strong> document for application <strong>{{ $gafId }}</strong>.
                                        </p>
                                        @if(!empty($reasons))
                                        <p style="color: #1e40af; font-size: 14px; margin: 8px 0 0 0;">
                                            <strong>Details:</strong> {{ implode(', ', $reasons) }}
                                        </p>
                                        @endif
                                        <p style="color: #1e40af; font-size: 14px; margin: 8px 0 0 0;">
                                            A recruitment officer will review your document manually. This is a normal part of the verification process and does not mean your document has been rejected.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #333; font-size: 15px; line-height: 1.6;">
                                Please allow some time for the manual review. You will receive a notification once the review is complete. No action is required from you unless you are contacted to re-upload your document.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ config('app.url') }}/applicant/documents"
                                           style="background: #1e40af; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-size: 14px; font-weight: bold; display: inline-block;">
                                            View Document Status
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">

                            <p style="color: #6b7280; font-size: 12px; line-height: 1.5;">
                                This is an automated message from the Defence Manpower Recruitment Management System (DMRMS). Do not reply to this email.<br><br>
                                For assistance, contact: <a href="mailto:recruitment@gaf.mil.gh" style="color: #1e40af;">recruitment@gaf.mil.gh</a> | Tel: +233 (0) 302 123 456<br><br>
                                Ghana Armed Forces — Defence Manpower Recruitment Management System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
