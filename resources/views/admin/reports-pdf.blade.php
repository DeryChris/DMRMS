<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DMRMS Report - {{ $generatedAt }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        h1 { font-size: 18px; color: #2D6A4F; text-align: center; margin-bottom: 5px; }
        h2 { font-size: 14px; color: #555; text-align: center; margin-top: 0; font-weight: normal; }
        .meta { text-align: center; font-size: 9px; color: #888; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #2D6A4F; color: white; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 9px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .stage-box { margin-top: 20px; }
        .stage-bar { display: flex; height: 20px; border-radius: 4px; overflow: hidden; margin: 10px 0; }
        .stage-item { padding: 2px 8px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #aaa; }
    </style>
</head>
<body>
    <h1>DMRMS Recruitment Report</h1>
    <h2>{{ $cycleName }}</h2>
    <div class="meta">Generated: {{ $generatedAt }} &mdash; {{ $applications->count() }} total applications</div>

    <h3>Stage Breakdown</h3>
    <table>
        <thead>
            <tr><th>Stage</th><th align="right">Count</th></tr>
        </thead>
        <tbody>
            @foreach($stageStats as $status => $count)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                <td align="right">{{ number_format($count) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; border-top: 2px solid #2D6A4F;">
                <td>Total</td>
                <td align="right">{{ number_format($stageStats->sum()) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Applicant List</h3>
    <table>
        <thead>
            <tr><th>#</th><th>GAF ID</th><th>Name</th><th>Region</th><th>Status</th><th>Cycle</th><th>Submitted</th></tr>
        </thead>
        <tbody>
            @foreach($applications as $i => $app)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $app->applicant?->gaf_id ?? 'N/A' }}</td>
                <td>{{ $app->applicant?->name ?? 'N/A' }}</td>
                <td>{{ $app->applicant?->region ?? 'N/A' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $app->status)) }}</td>
                <td>{{ $app->cycle?->name ?? 'N/A' }}</td>
                <td>{{ $app->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Ghana Armed Forces &mdash; Digital Military Recruitment Management System &mdash; Report generated {{ $generatedAt }}</div>
</body>
</html>
