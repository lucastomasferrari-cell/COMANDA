<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .wrap {
            padding: 20px;
        }

        .header {
            border: 1px solid #e6edf5;
            border-radius: 12px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .header-top {
            background: #f8fbff;
            border-bottom: 1px solid #eaf0f7;
            padding: 10px 12px;
        }

        .header-top table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 34px;
            height: 34px;
        }

        .brand-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 11px;
            margin-top: 1px;
        }

        .meta {
            text-align: right;
            color: #64748b;
            font-size: 11px;
        }

        .meta strong {
            color: #0f172a;
            font-size: 12px;
            display: block;
            margin-top: 2px;
        }

        .header-body {
            padding: 10px 12px;
        }

        .title {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-radius: 10px;
            overflow: hidden;
        }

        table.data th, table.data td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.data th {
            background: #f8fafc;
            text-align: left;
            font-weight: 700;
            color: #334155;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="header-top">
            <table>
                <tr>
                    <td>
                        <div class="brand">
                            <img class="logo" src="{{ getLogoBase64() }}" alt="logo">
                            <div>
                                <p class="brand-title">{{ setting('app_name') ?: config('app.name') }}</p>
                                <div class="brand-subtitle">{{ __('hr::payroll_reports.payroll_reports') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="meta">
                        {{ trans('admin::admin.table.generated_at') }}
                        <strong>{{ dateTimeFormat($generatedAt) }}</strong>
                    </td>
                </tr>
            </table>
        </div>
        <div class="header-body">
            <p class="title">{{ $title }}</p>
        </div>
    </div>

    <table class="data">
        <thead>
        <tr>
            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($headers as $header)
                    <td>{{ $row[$header] ?? '' }}</td>
                @endforeach
            </tr>
        @empty
            <tr style="text-align: center">
                <td colspan="{{ count($headers) }}">{{ __('admin::admin.table.no_data_available') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
