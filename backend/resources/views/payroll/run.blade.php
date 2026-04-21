@php use Modules\Support\Money; @endphp
    <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('hr::payroll_runs.payroll_run') }} #{{ $run->id }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            background: #ffffff;
            font-size: 12px;
        }

        .page {
            padding: 16px;
        }

        .header-card {
            border: 1px solid #e9edf3;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #ffffff;
        }

        .header-banner {
            background: #f7fafd;
            color: #1f2937;
            padding: 12px 16px;
            border-bottom: 1px solid #edf2f7;
        }

        .header-content {
            padding: 12px 14px 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand img {
            width: 50px;
            height: 50px;
        }

        .brand-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            color: #0f172a;
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 12px;
        }

        .run-id {
            text-align: right;
        }

        .run-id .label {
            color: #64748b;
            font-size: 11px;
        }

        .run-id .value {
            font-size: 17px;
            font-weight: 800;
            margin-top: 2px;
            color: #0f172a;
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            background: #e8f1fb;
        }

        .meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 0 -8px 4px;
        }

        .meta-box {
            border: 1px dashed #edf1f6;
            border-radius: 10px;
            padding: 8px 10px;
            background: #fbfdff;
        }

        .meta-box .label {
            color: #64748b;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .meta-box .value {
            font-size: 13px;
            font-weight: 700;
        }

        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 0 -8px 12px;
        }

        .summary-box {
            border-radius: 10px;
            padding: 9px 10px;
            border: 1px dashed #edf1f6;
        }

        .summary-box .label {
            color: #64748b;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .summary-box .value {
            font-size: 14px;
            font-weight: 800;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin: 10px 0 8px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        table.data thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 10px;
            letter-spacing: .03em;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 8px;
            text-align: left;
        }

        table.data tbody td {
            border-bottom: 1px solid #eef2f7;
            padding: 8px 8px;
            vertical-align: middle;
        }

        table.data tbody tr:nth-child(even) {
            background: #fcfdff;
        }

        table.data tbody tr:last-child td {
            border-bottom: 0;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #64748b;
            font-size: 11px;
        }

    </style>
</head>
<body>
@php
    $currency = $run->branch?->currency ?? setting('default_currency');
    $grossTotal = $run->payslips->sum(fn($row) => (float) $row->gross_amount->amount());
    $deductionsTotal = $run->payslips->sum(fn($row) => (float) $row->deductions_amount->amount());
    $netTotal = $run->payslips->sum(fn($row) => (float) $row->net_amount->amount());
    $employerTotal = $run->payslips->sum(fn($row) => (float) $row->employer_cost_amount->amount());
@endphp

<div class="page">
    <div class="header-card">
        <div class="header-banner">
            <table class="header-table">
                <tr>
                    <td>
                        <div class="brand">
                            <img alt="logo" src="{{ getLogoBase64() }}">
                            <div>
                                <p class="brand-title">{{ setting('app_name') ?: config('app.name') }}</p>
                                <div class="brand-subtitle">{{ __('hr::payroll_runs.payroll_run') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="run-id">
                        <div class="value">#{{ $run->id }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="header-content">
            <table class="meta-grid">
                <tr>
                    <td class="meta-box">
                        <div class="label">{{ __('hr::payroll_runs.table.period') }}</div>
                        <div class="value">{{ $run->period?->name }}</div>
                    </td>
                    <td class="meta-box">
                        <div class="label">{{ __('hr::payroll_runs.table.branch') }}</div>
                        <div class="value">{{ $run->branch?->name }}</div>
                    </td>
                    <td class="meta-box">
                        <div class="label">{{ __('hr::payroll_runs.table.status') }}</div>
                        <div class="value">{{ $run->status?->trans() ?? '-' }}</div>
                    </td>
                    <td class="meta-box" colspan="2">
                        <div class="label">{{ __('hr::payroll_runs.table.business_status') }}</div>
                        <div class="value">{{ $run->business_status?->trans() ?? '-' }}</div>
                    </td>
                </tr>
            </table>

            <div class="muted">{{ __('admin::admin.table.created_at') }}
                : {{ optional($run->created_at)->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <table class="summary-grid">
        <tr>
            <td class="summary-box">
                <div class="label">{{ __('hr::payroll_payslips.payroll_payslips') }}</div>
                <div class="value">{{ $run->payslips->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="label">{{ __('hr::payroll_payslips.table.gross_amount') }}</div>
                <div class="value">{{ (new Money($grossTotal, $currency))->format() }}</div>
            </td>
            <td class="summary-box">
                <div class="label">{{ __('hr::payroll_payslips.table.deductions_amount') }}</div>
                <div class="value">{{ (new Money($deductionsTotal, $currency))->format() }}</div>
            </td>
            <td class="summary-box net">
                <div class="label">{{ __('hr::payroll_payslips.table.net_amount') }}</div>
                <div class="value">{{ (new Money($netTotal, $currency))->format() }}</div>
            </td>
            <td class="summary-box">
                <div class="label">{{ __('hr::payroll_payslips.table.employer_cost_amount') }}</div>
                <div class="value">{{ (new Money($employerTotal, $currency))->format() }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">{{ __('hr::payroll_payslips.payroll_payslips') }}</div>
    <table class="data">
        <thead>
        <tr>
            <th>#</th>
            <th>{{ __('hr::payroll_payslips.table.employee') }}</th>
            <th>{{ __('hr::payroll_payslips.table.salary_type') }}</th>
            <th>{{ __('hr::payroll_payslips.table.status') }}</th>
            <th class="right">{{ __('hr::payroll_payslips.table.gross_amount') }}</th>
            <th class="right">{{ __('hr::payroll_payslips.table.deductions_amount') }}</th>
            <th class="right">{{ __('hr::payroll_payslips.table.net_amount') }}</th>
            <th class="right">{{ __('hr::payroll_payslips.table.employer_cost_amount') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($run->payslips as $payslip)
            <tr>
                <td>{{ $payslip->reference_id ?: ('#'.$payslip->id) }}</td>
                <td>{{ $payslip->employee?->code ? ($payslip->employee->code . ' - ') : '' }}{{ $payslip->employee?->name }}</td>
                <td>{{ $payslip->salary_type?->trans() ?? '-' }}</td>
                <td>{{ $payslip->status?->trans() ?? '-' }}</td>
                <td class="right">{{ $payslip->gross_amount->format() }}</td>
                <td class="right">{{ $payslip->deductions_amount->format() }}</td>
                <td class="right">{{ $payslip->net_amount->format() }}</td>
                <td class="right">{{ $payslip->employer_cost_amount->format() }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center;">{{ __('admin::admin.table.no_data_available') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
