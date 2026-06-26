<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Offer Letter - Ghana Armed Forces</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.6; }
        .header { text-align: center; border-bottom: 3px solid #2D6A4F; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { font-size: 20px; color: #2D6A4F; margin: 5px 0; }
        .header h2 { font-size: 14px; color: #555; margin: 0; font-weight: normal; }
        .header .emblem { font-size: 36px; margin-bottom: 5px; }
        .ref { text-align: right; font-size: 9px; color: #888; margin-bottom: 20px; }
        .greeting { font-size: 12px; margin-bottom: 15px; }
        .body-text { margin-bottom: 20px; text-align: justify; }
        .details { margin: 20px 0; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
        .details td:first-child { font-weight: bold; width: 140px; color: #555; }
        .instructions { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #2D6A4F; border-radius: 4px; }
        .instructions h3 { font-size: 12px; color: #2D6A4F; margin: 0 0 8px 0; }
        .instructions ul { margin: 0; padding-left: 18px; font-size: 10px; }
        .instructions li { margin-bottom: 4px; }
        .signature { margin-top: 30px; padding-top: 20px; }
        .signature p { margin: 2px 0; font-size: 10px; }
        .signature .name { font-weight: bold; font-size: 11px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #aaa; border-top: 1px solid #eee; padding-top: 5px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 80px; color: rgba(45, 106, 79, 0.04); font-weight: bold; z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">GAF</div>

    <div class="header">
        <div class="emblem">&#9733;</div>
        <h1>GHANA ARMED FORCES</h1>
        <h2>Directorate of Recruiting &amp; Selection</h2>
        <p>Burma Camp, Accra - Ghana</p>
    </div>

    <div class="ref">
        <p>Ref: GAF/REC/{{ $application->id }}/{{ date('Y') }}</p>
        <p>Date: {{ $generatedAt }}</p>
    </div>

    <div class="greeting">
        <p><strong>Dear {{ $applicant->name ?? 'Candidate' }},</strong></p>
    </div>

    <div class="body-text">
        <p>It is my distinct honour and privilege to inform you that after a rigorous and competitive selection process, you have been found suitable for recruitment into the <strong>Ghana Armed Forces</strong>.</p>
        <p>Your determination, discipline, and performance throughout the recruitment stages have earned you a place among the selected candidates for the {{ $cycle->name ?? 'current' }} recruitment cycle.</p>
    </div>

    <div class="details">
        <h3 style="font-size: 12px; color: #2D6A4F; margin-bottom: 8px;">Candidate Details</h3>
        <table>
            <tr><td>Full Name</td><td>{{ $applicant->name }}</td></tr>
            <tr><td>GAF ID</td><td>{{ $applicant->gaf_id ?? 'N/A' }}</td></tr>
            <tr><td>Date of Birth</td><td>{{ $applicant->date_of_birth?->format('F j, Y') ?? 'N/A' }}</td></tr>
            <tr><td>Gender</td><td>{{ ucfirst($applicant->gender ?? 'N/A') }}</td></tr>
            <tr><td>Region</td><td>{{ $applicant->region ?? 'N/A' }}</td></tr>
            <tr><td>Recruitment Cycle</td><td>{{ $cycle->name ?? 'N/A' }}</td></tr>
            <tr><td>Date of Offer</td><td>{{ $generatedAt }}</td></tr>
        </table>
    </div>

    <div class="instructions">
        <h3>Reporting Instructions</h3>
        <ul>
            <li>Report to the Ghana Armed Forces Recruiting Depot on the date specified in your appointment letter.</li>
            <li>Bring this offer letter, your National ID, and all original educational certificates.</li>
            <li>Wear appropriate sports attire and footwear for physical assessments.</li>
            <li>Report by 0600 hours. Late arrival will result in disqualification.</li>
            <li>Do not bring any weapons, alcohol, or unauthorized items.</li>
        </ul>
    </div>

    <div class="body-text">
        <p>Congratulations on this achievement. We look forward to welcoming you into the Ghana Armed Forces family. Your journey to serve and protect our nation begins here.</p>
    </div>

    <div class="signature">
        <p>Yours faithfully,</p>
        <br>
        <p class="name">For: Director of Recruiting</p>
        <p>Ghana Armed Forces</p>
        <p>Burma Camp, Accra</p>
    </div>

    <div class="footer">
        Ghana Armed Forces — Digital Military Recruitment Management System — This is a computer-generated document
    </div>
</body>
</html>
