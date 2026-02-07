<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ $report['month'] }}</title>
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
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-item {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            text-align: center;
        }
        .summary-item h3 {
            color: #6b7280;
            font-size: 11px;
            margin: 0 0 8px 0;
            font-weight: 600;
            text-transform: uppercase;
        }
        .summary-item .amount {
            color: #1e40af;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-item .change {
            font-size: 10px;
            color: #059669;
        }
        .summary-item .change.negative {
            color: #dc2626;
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
            border: 1px solid #e5e7eb;
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
        .amount-cell {
            text-align: right;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Prophetic Life Embassy</h1>
        <h2>Financial Report - {{ $report['month'] }}</h2>
        <div class="period">
            Period: {{ $report['period']['start'] }} to {{ $report['period']['end'] }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-item">
            <h3>Total Contributions</h3>
            <div class="amount">GHS {{ number_format($report['contributions']['total'], 2) }}</div>
            <div class="change {{ $report['contributions']['change_percent'] < 0 ? 'negative' : '' }}">
                {{ $report['contributions']['change_percent'] > 0 ? '+' : '' }}{{ $report['contributions']['change_percent'] }}% from last month
            </div>
            <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                {{ $report['contributions']['count'] }} contributions
            </div>
        </div>
        <div class="summary-item">
            <h3>Total Expenses</h3>
            <div class="amount">GHS {{ number_format($report['expenses']['total'], 2) }}</div>
            <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                Approved: {{ $report['expenses']['approved'] }} | Pending: {{ $report['expenses']['pending'] }}
            </div>
        </div>
        <div class="summary-item">
            <h3>Net Position</h3>
            <div class="amount {{ $report['net'] < 0 ? 'negative' : '' }}" style="color: {{ $report['net'] >= 0 ? '#059669' : '#dc2626' }};">
                GHS {{ number_format($report['net'], 2) }}
            </div>
            <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                Contributions - Expenses
            </div>
        </div>
    </div>

    <h3 class="section-title">Top Contributors</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 60%;">Name</th>
                <th style="width: 40%; text-align: right;">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['top_contributors'] as $contributor)
            <tr>
                <td>{{ $contributor['name'] }}</td>
                <td class="amount-cell">{{ number_format($contributor['amount'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="section-title">Expense Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 60%;">Expense Type</th>
                <th style="width: 40%; text-align: right;">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['expenses']['breakdown'] as $expense)
            <tr>
                <td>{{ $expense['type'] }}</td>
                <td class="amount-cell">{{ number_format($expense['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #e5e7eb; font-weight: bold;">
                <td>Total</td>
                <td class="amount-cell">{{ number_format($report['expenses']['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ $generated_at }} by {{ $generated_by }}</p>
        <p>This is a computer-generated report from Prophetic Life Embassy Church Management System.</p>
        <p>For inquiries, contact the finance department.</p>
    </div>
</body>
</html>
