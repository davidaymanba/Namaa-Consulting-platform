<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Operational Report</h2>
    <p>From {{ $from->toDateString() }} to {{ $to->toDateString() }}</p>
    <p>Premiums: {{ number_format($premiumsTotal, 2) }} SAR | Claims: {{ number_format($claimsTotal, 2) }} SAR | Loss Ratio: {{ number_format($lossRatio, 2) }}%</p>

    <h3>Production by Type</h3>
    <table>
        <thead><tr><th>Type</th><th>Policies</th><th>Premiums</th></tr></thead>
        <tbody>@foreach ($production as $row)<tr><td>{{ $row->type }}</td><td>{{ $row->policies_count }}</td><td>{{ number_format($row->premium_total, 2) }}</td></tr>@endforeach</tbody>
    </table>

    <h3>Claims by Status</h3>
    <table>
        <thead><tr><th>Status</th><th>Claims</th><th>Approved</th></tr></thead>
        <tbody>@foreach ($claims as $row)<tr><td>{{ $row->status }}</td><td>{{ $row->claims_count }}</td><td>{{ number_format($row->approved_total, 2) }}</td></tr>@endforeach</tbody>
    </table>
</body>
</html>
