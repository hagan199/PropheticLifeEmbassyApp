<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header h2 {
            color: #6b7280;
            margin: 0;
            font-size: 18px;
            font-weight: normal;
        }
        .header .period {
            color: #9ca3af;
            font-size: 11px;
            margin-top: 5px;
        }
        .summary {
            background-color: #eff6ff;
            border: 1px solid #2563eb;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .summary .stat {
            display: inline-block;
            margin: 0 20px;
        }
        .summary .stat .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .summary .stat .value {
            font-size: 24px;
            color: #1e40af;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
        }
        td {
            border: 1px solid: #e5e7eb;
            padding: 8px;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .section-title {
            color: #1e40af;
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 10px;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
        .count-cell {
            text-align: right;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Prophetic Life Embassy</h1>
        <h2>Attendance Report</h2>
        <div class="period">
            Period: {{ $report['period']['start'] }} to {{ $report['period']['end'] }}
        </div>
    </div>

    <div class="summary">
        <div class="stat">
            <div class="label">Total Attendance</div>
            <div class="value">{{ number_format($report['total_attendance']) }}</div>
        </div>
        <div class="stat">
            <div class="label">Average Per Service</div>
            <div class="value">{{ number_format($report['average_per_service'], 1) }}</div>
        </div>
    </div>

    <h3 class="section-title">Attendance by Service Type</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Service Type</th>
                <th style="width: 30%; text-align: right;">Total Attendance</th>
                <th style="width: 30%; text-align: right;">Services Held</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['by_service_type'] as $service)
            <tr>
                <td>{{ $service->service_type }}</td>
                <td class="count-cell">{{ number_format($service->total) }}</td>
                <td class="count-cell">{{ number_format($service->records) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="section-title">Weekly Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Week</th>
                <th style="width: 30%; text-align: right;">Attendance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['weekly_breakdown'] as $week)
            <tr>
                <td>{{ $week['week'] }}</td>
                <td class="count-cell">{{ number_format($week['count']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ $generated_at }} by {{ $generated_by }}</p>
        <p>This is a computer-generated report from Prophetic Life Embassy Church Management System.</p>
    </div>
</body>
</html>
