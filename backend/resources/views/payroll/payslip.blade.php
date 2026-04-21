@php use Illuminate\Support\Carbon; @endphp
        <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('hr::payroll_payslips.template.title') }} {{ $payslip->reference_id ?: ('#' . $payslip->id) }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 12px;
        }

        .page {
            padding: 18px;
        }

        .card {
            border: 1px solid #e6edf5;
            border-radius: 12px;
            background: #ffffff;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .header {
            border-bottom: 1px solid #eaf0f7;
            background: #f8fbff;
            padding: 12px 14px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-wrap {
            width: 70%;
        }

        .logo {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            vertical-align: middle;
        }

        .brand-text {
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }

        .app-name {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
        }

        .subtitle {
            font-size: 11px;
            color: #64748b;
        }

        .ref-wrap {
            text-align: right;
        }

        .ref-label {
            color: #64748b;
            font-size: 11px;
        }

        .ref-value {
            font-size: 18px;
            font-weight: 800;
            margin-top: 2px;
        }

        .section {
            padding: 10px 12px;
        }

        .section-title {
            margin: 0 0 7px 0;
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;

        }

        .info-box {
            border: 1px solid #eef3f8;
            border-radius: 10px;
            background: #fbfdff;
            padding: 8px 10px;
        }

        .info-label {
            color: #64748b;
            font-size: 10px;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .emp-avatar-cell {
            width: 62px;
        }

        .emp-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #dbe5f1;
            background: #ffffff;
        }

        .avatar-fallback {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e8f1fb;
            color: #0b3b68;
            border: 1px solid #dbe5f1;
            text-align: center;
            line-height: 48px;
            font-size: 16px;
            font-weight: 800;
        }

        .line-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5ecf4;
            border-radius: 10px;
            overflow: hidden;
        }

        .line-table th {
            background: #f8fafc;
            border-bottom: 1px solid #e5ecf4;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .03em;
            font-size: 10px;
            font-weight: 800;
            padding: 8px;
            text-align: left;
        }

        .line-table td {
            border-bottom: 1px solid #eef3f8;
            padding: 8px;
            vertical-align: middle;
        }

        .line-table tr:last-child td {
            border-bottom: 0;
        }

        .line-table tr:nth-child(even) {
            background: #fcfdff;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .totals {
            width: 46%;
            margin-left: auto;
            border: 1px solid #e5ecf4;
            border-radius: 10px;
            overflow: hidden;
        }

        .totals td {
            padding: 9px 10px;
            border-bottom: 1px solid #edf2f7;
            font-weight: 700;
        }

        .totals tr:last-child td {
            border-bottom: 0;
        }

        .net-row td {
            background: #eefbf3;
            color: #065f46;
            font-weight: 800;
        }
    </style>
</head>
<body>
@php

    $employee = $payslip->employee;
    $bank = (array) ($employee?->bank_information ?? []);
    $employeeName = $employee?->name ?? '-';
    $branchName = $payslip->run?->branch?->name ?? '-';
    $periodName = $payslip->run?->period?->name ?? '-';
    $designationName = data_get($employee?->designation, 'name', '-');
    $departmentName = data_get($employee?->department, 'name', '-');
    $employeePhotoUrl = data_get($employee?->profile_photo_path, 'url');
    $avatarText = strtoupper(substr((string) $employeeName, 0, 1) ?: 'E');
    $snapshot = (array) ($payslip->calculation_snapshot ?? []);

    $periodFrom = $payslip->run?->period?->date_from;
    $periodTo = $payslip->run?->period?->date_to;
    $daysInMonth = $periodFrom ? Carbon::parse($periodFrom)->daysInMonth : null;
    $periodDays = ($periodFrom && $periodTo)
        ? Carbon::parse($periodFrom)->diffInDays(Carbon::parse($periodTo)) + 1
        : null;

    $daysWorked = data_get($snapshot, 'days_worked')
        ?? data_get($snapshot, 'worked_days_total')
        ?? data_get($snapshot, 'present_days_total');

    $paidLeaveDays = data_get($snapshot, 'leave_paid_days_total');
    $unpaidLeaveDays = data_get($snapshot, 'leave_unpaid_days_total');
    $leaveDays = ($paidLeaveDays?:0) + ($unpaidLeaveDays?:0);
    $absentDays = data_get($snapshot, 'absent_days_total');

    if ($daysWorked === null && is_numeric($periodDays)) {
        $daysWorked = (float)$periodDays - (float)($absentDays ?? 0) - (float)($leaveDays ?? 0);
        if ($daysWorked < 0) {
            $daysWorked = 0;
        }
    }

    $workedMinutes = data_get($snapshot, 'worked_minutes_total');
    $overtimeMinutes = data_get($snapshot, 'overtime_minutes_approved_total');


    $formatNumber = fn ($value) => is_numeric($value) ? number_format((float) $value, 2, '.', '') : '-';
    $formatHours = fn ($minutes) => is_numeric($minutes) ? number_format(((float) $minutes / 60), 2, '.', '') : '-';
    $formatMinutesHhMm = function ($minutes): string {
        if (!is_numeric($minutes)) {
            return '-';
        }

        $total = (int) round((float) $minutes);
        $hours = intdiv($total, 60);
        $mins = $total % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    };

    $earnings = $payslip->lines->filter(fn($line) => $line->type?->value === 'earning')->values();
    $deductions = $payslip->lines->filter(fn($line) => $line->type?->value === 'deduction')->values();
@endphp

<div class="page">
    <div class="card">
        <div class="header">
            <table class="table">
                <tr>
                    <td class="brand-wrap">
                        <img class="logo" src="{{ getLogoBase64() }}" alt="logo">
                        <div class="brand-text">
                            <p class="app-name">{{ setting('app_name') ?: config('app.name') }}</p>
                            <span class="subtitle">{{ __('hr::payroll_payslips.template.title') }}</span>
                        </div>
                    </td>
                    <td class="ref-wrap">
                        <div class="ref-label">{{ __('hr::payroll_payslips.template.reference_id') }}</div>
                        <div class="ref-value">{{ $payslip->reference_id ?: ('#' . $payslip->id) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table class="info-grid">
                <tr>
                    <td class="info-box" colspan="2">
                        <table class="table">
                            <tr>
                                <td class="emp-avatar-cell">
                                    @if($employeePhotoUrl)
                                        <img class="emp-avatar" src="{{ $employeePhotoUrl }}" alt="employee-photo">
                                    @else
                                        <div class="avatar-fallback">{{ $avatarText }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="info-label">{{ __('hr::payroll_payslips.template.employee') }}</div>
                                    <div class="info-value">{{ $employeeName }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.employee_code') }}</div>
                        <div class="info-value">{{ $employee?->code ?? '-' }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.department') }}</div>
                        <div class="info-value">{{ $departmentName }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.designation') }}</div>
                        <div class="info-value">{{ $designationName }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.branch') }}</div>
                        <div class="info-value">{{ $branchName }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.period') }}</div>
                        <div class="info-value">{{ $periodName }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.salary_type') }}</div>
                        <div class="info-value">{{ $payslip->salary_type?->trans() ?? '-' }}</div>
                    </td>
                    <td class="info-box" colspan="2">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.status') }}</div>
                        <div class="info-value">{{ $payslip->status?->trans() ?? '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section">
            <p class="section-title">{{ __('hr::payroll_payslips.template.pay_details') }}</p>
            <table class="info-grid">
                <tr>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.days_in_month') }}</div>
                        <div class="info-value">{{ $daysInMonth ?? '-' }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.period_days') }}</div>
                        <div class="info-value">{{ $periodDays ?? '-' }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.days_worked') }}</div>
                        <div class="info-value">{{ $formatNumber($daysWorked) }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.leave_days') }}</div>
                        <div class="info-value">{{ $formatNumber($leaveDays) }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.paid_leave_days') }}</div>
                        <div class="info-value">{{ $formatNumber($paidLeaveDays) }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.unpaid_leave_days') }}</div>
                        <div class="info-value">{{ $formatNumber($unpaidLeaveDays) }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.absent_days') }}</div>
                        <div class="info-value">{{ $formatNumber($absentDays) }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.overtime_hours') }}</div>
                        <div class="info-value">{{ $formatHours($overtimeMinutes) }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-box" colspan="4">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.worked_hours') }}</div>
                        <div class="info-value">{{ $formatMinutesHhMm($workedMinutes) }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section">
            <p class="section-title">{{ __('hr::payroll_payslips.template.bank_information') }}</p>
            <table class="info-grid">
                <tr>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.bank_name') }}</div>
                        <div class="info-value">{{ data_get($bank, 'bank_name', '-') }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.bank_branch_name') }}</div>
                        <div class="info-value">{{ data_get($bank, 'branch_name', '-') }}</div>
                    </td>
                    <td class="info-box">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.bank_account_name') }}</div>
                        <div class="info-value">{{ data_get($bank, 'account_name', '-') }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-box" colspan="2">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.bank_iban') }}</div>
                        <div class="info-value">{{ data_get($bank, 'iban', '-') }}</div>
                    </td>
                    <td class="info-box" colspan="2">
                        <div class="info-label">{{ __('hr::payroll_payslips.template.bank_swift_code') }}</div>
                        <div class="info-value">{{ data_get($bank, 'swift_code', '-') }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section">
            <p class="section-title">{{ __('hr::payroll_payslips.template.section_earning') }}</p>
            <table class="line-table">
                <thead>
                <tr>
                    <th>{{ __('hr::payroll_payslips.template.component') }}</th>
                    <th>{{ __('hr::payroll_payslips.template.quantity') }}</th>
                    <th>{{ __('hr::payroll_payslips.template.rate') }}</th>
                    <th>{{ __('hr::payroll_payslips.template.amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($earnings as $line)
                    <tr>
                        <td>{{ $line->name }}</td>
                        <td>{{ is_null($line->quantity) ? '-' : number_format((float)$line->quantity, 2, '.', '') }}</td>
                        <td>{{ is_null($line->rate) ? '-' : $line->rate->format() }}</td>
                        <td>{{ $line->amount->format() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">{{ __('hr::payroll_payslips.template.no_data') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section">
            <p class="section-title">{{ __('hr::payroll_payslips.template.section_deduction') }}</p>
            <table class="line-table">
                <thead>
                <tr>
                    <th>{{ __('hr::payroll_payslips.template.component') }}</th>
                    <th>{{ __('hr::payroll_payslips.template.quantity') }}</th>
                    <th>{{ __('hr::payroll_payslips.template.rate') }}</th>
                    <th>{{ __('hr::payroll_payslips.template.amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($deductions as $line)
                    <tr>
                        <td>{{ $line->name }}</td>
                        <td>{{ is_null($line->quantity) ? '-' : number_format((float)$line->quantity, 2, '.', '') }}</td>
                        <td>{{ is_null($line->rate) ? '-' : $line->rate->format() }}</td>
                        <td>{{ $line->amount->format() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">{{ __('hr::payroll_payslips.template.no_data') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <table class="totals">
        <tbody>
        <tr>
            <td>{{ __('hr::payroll_payslips.template.gross') }}</td>
            <td class="right">{{ $payslip->gross_amount->format() }}</td>
        </tr>
        <tr>
            <td>{{ __('hr::payroll_payslips.template.deductions') }}</td>
            <td class="right">{{ $payslip->deductions_amount->format() }}</td>
        </tr>
        <tr class="net-row">
            <td>{{ __('hr::payroll_payslips.template.net') }}</td>
            <td class="right">{{ $payslip->net_amount->format() }}</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
