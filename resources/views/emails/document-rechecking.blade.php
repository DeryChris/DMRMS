<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Document Being Rechecked' }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                    {{-- Header --}}
                    <tr>
                        <td style="background: #854d0e; padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px;">Ghana Armed Forces</h1>
                            <p style="color: #fde68a; margin: 8px 0 0 0; font-size: 14px;">Document Re-Verification in Progress</p>
                        </td>
                    </tr>
                    {{-- Body --}}
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333; font-size: 15px; line-height: 1.6;">Dear <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>,</p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="color: #92400e; font-size: 16px; margin: 0; font-weight: bold;">🔄 {{ $documentType }} — Being Rechecked</p>
                                        <p style="color: #92400e; font-size: 14px; margin: 8px 0 0 0;">
                                            The initial AI verification confidence for your <strong>{{ $documentType }}</strong> document (application <strong>{{ $gafId }}</strong>) was <strong>{{ $confidencePct }}%</strong>, which requires a second look.
                                        </p>
                                        <p style="color: #92400e; font-size: 14px; margin: 8px 0 0 0;">
                                            The system is automatically rechecking the document (attempt <strong>{{ $attempt }}</strong> of <strong>{{ $maxRetries }}</strong>). You will be notified once the verification is complete.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #333; font-size: 15px; line-height: 1.6;">No action is required from you at this time. Please check the portal for updates.</p>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ config('app.url') }}/applicant/documents"
                                           style="background: #854d0e; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-size: 14px; font-weight: bold; display: inline-block;">
                                            Check Document Status
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">

                            <p style="color: #6b7280; font-size: 12px; line-height: 1.5;">
                                This is an automated message from the Defence Manpower Recruitment Management System (DMRMS). Do not reply to this email.<br><br>
                                For assistance, contact: <a href="mailto:recruitment@gaf.mil.gh" style="color: #854d0e;">recruitment@gaf.mil.gh</a> | Tel: +233 (0) 302 123 456<br><br>
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
