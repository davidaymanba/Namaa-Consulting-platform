<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #111827; }
        .header { margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: bold; color: #0f172a; }
        .sub { color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: right; }
        th { background: #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">وثيقة تأمين - {{ $policy->policy_no }}</div>
        <div class="sub">شركة نماء للاستشارات | تاريخ الإصدار: {{ $policy->issued_at?->format('Y-m-d') }}</div>
    </div>

    <table>
        <tr><th>العميل</th><td>{{ $policy->client->name }}</td></tr>
        <tr><th>الوسيط</th><td>{{ $policy->broker?->name ?? '-' }}</td></tr>
        <tr><th>النوع</th><td>{{ config('insurance.insurance_types')[$policy->type] ?? $policy->type }}</td></tr>
        <tr><th>القسط الصافي</th><td>{{ number_format($policy->net_premium, 2) }} SAR</td></tr>
        <tr><th>الفترة</th><td>{{ $policy->start_date->format('Y-m-d') }} إلى {{ $policy->end_date->format('Y-m-d') }}</td></tr>
    </table>

    <h3>بيانات المركبة</h3>
    <table>
        <tr><th>رقم اللوحة</th><td>{{ $policy->data['plate_number'] ?? '-' }}</td><th>الشركة</th><td>{{ $policy->data['make'] ?? '-' }}</td></tr>
        <tr><th>الموديل</th><td>{{ $policy->data['model'] ?? '-' }}</td><th>سنة الصنع</th><td>{{ $policy->data['year'] ?? '-' }}</td></tr>
        <tr><th>القيمة السوقية</th><td>{{ number_format($policy->data['market_value'] ?? 0, 2) }}</td><th>اسم السائق</th><td>{{ $policy->data['driver_name'] ?? '-' }}</td></tr>
    </table>

    <h3>جدول الأقساط</h3>
    <table>
        <thead><tr><th>#</th><th>الاستحقاق</th><th>المبلغ</th><th>الحالة</th></tr></thead>
        <tbody>
        @foreach ($policy->installments as $ins)
            <tr>
                <td>{{ $ins->sequence }}</td>
                <td>{{ $ins->due_date->format('Y-m-d') }}</td>
                <td>{{ number_format($ins->amount, 2) }}</td>
                <td>{{ strtoupper($ins->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
