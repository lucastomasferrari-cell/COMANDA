@php
    use Modules\Support\RTLDetector;
    $defaultLocale = locale();
    $documentLocale = strtolower($defaultLocale ?: config('app.locale', 'en'));
    $documentDirection = RTLDetector::detect($defaultLocale) ? 'rtl' : 'ltr';

    $paperWidthMm = (int)($profile['paper_width_mm'] ?? 80);
    $paperPixelWidth = (int)($profile['pixel_width'] ?? ($paperWidthMm <= 58 ? 384 : 576));
    $isNarrow = $paperWidthMm <= 58;
    $edgeSafeInsetMm = $isNarrow ? 1.8 : 2.2;
    $receiptRenderWidthMm = max(40, $paperWidthMm - ($edgeSafeInsetMm * 2));
    $receiptRenderPixelWidth = max(280, (int)floor($paperPixelWidth * ($receiptRenderWidthMm / max($paperWidthMm, 1))));

    $baseFontSize = $isNarrow ? 10.5 : 11.5;
    $smallFontSize = $isNarrow ? 9 : 10;
    $microFontSize = $isNarrow ? 8.2 : 9;
    $titleFontSize = $isNarrow ? 15 : 17;
    $contentPaddingMm = $isNarrow ? 2.2 : 3.1;

    $toFileUrl = function (?string $path): ?string {
        if (empty($path)) {
            return null;
        }

        $normalized = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        return str_starts_with($normalized, '/')
            ? 'file://' . $normalized
            : 'file:///' . $normalized;
    };

    $toFontData = function (?string $path): ?string {
        if (empty($path) || !is_readable($path)) {
            return null;
        }

        return base64_encode(file_get_contents($path));
    };

    $fontDefinitions = [
        'cairo' => config('printer.fonts.cairo', []),
        'inter' => config('printer.fonts.inter', []),
        'noto_sans_arabic' => config('printer.fonts.noto_sans_arabic', []),
    ];

    $fontFaces = collect($fontDefinitions)->map(function (array $font) use ($toFontData, $toFileUrl): array {
        $family = (string)($font['family'] ?? '');
        $regularPath = $font['regular'] ?? null;
        $boldPath = $font['bold'] ?? $regularPath;

        return [
            'family' => $family,
            'regular_data' => $regularPath ? $toFontData($regularPath) : null,
            'regular_url' => $regularPath ? $toFileUrl($regularPath) : null,
            'bold_data' => $boldPath ? $toFontData($boldPath) : null,
            'bold_url' => $boldPath ? $toFileUrl($boldPath) : null,
        ];
    });
@endphp
    <!doctype html>
<html lang="{{ str_replace('_', '-', $documentLocale) }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        @foreach($fontFaces as $fontFace)
            @if($fontFace['family'] !== '' && (!empty($fontFace['regular_data']) || !empty($fontFace['regular_url'])))
        @font-face {
            font-family: "{{ $fontFace['family'] }}";
            src: @if(!empty($fontFace['regular_data']))
                url("data:font/ttf;base64,{{ $fontFace['regular_data'] }}") format("truetype")
                @else
                url("{{ $fontFace['regular_url'] }}") format("truetype")
        @endif
;
            font-weight: 400;
            font-style: normal;
        }

        @endif

        @if($fontFace['family'] !== '' && (!empty($fontFace['bold_data']) || !empty($fontFace['bold_url'])))
@font-face {
            font-family: "{{ $fontFace['family'] }}";
            src: @if(!empty($fontFace['bold_data']))
                url("data:font/ttf;base64,{{ $fontFace['bold_data'] }}") format("truetype")
                @else
                url("{{ $fontFace['bold_url'] }}") format("truetype")
        @endif
;
            font-weight: 700;
            font-style: normal;
        }

        @endif
        @endforeach

        :root {
            --receipt-width-mm: {{ $paperWidthMm }};
            --receipt-width-px: {{ $paperPixelWidth }};
            --receipt-render-width-mm: {{ $receiptRenderWidthMm }};
            --receipt-render-width-px: {{ $receiptRenderPixelWidth }};
            --receipt-padding-mm: {{ $contentPaddingMm }};
            --receipt-padding-inline-mm: {{ $isNarrow ? '3.4' : '4.2' }};
            --font-base: {{ $baseFontSize }}px;
            --font-small: {{ $smallFontSize }}px;
            --font-micro: {{ $microFontSize }}px;
            --font-title: {{ $titleFontSize }}px;
            --ink: #000000;
            --paper: #ffffff;
            --rule: 2px solid #000000;
            --rule-soft: 1px dashed #000000;
            --line-height: 1.35;
            --section-gap: {{ $isNarrow ? '2.2mm' : '2.8mm' }};
        }

        @page {
            size: {{ $paperWidthMm }}mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: var(--paper);
        }

        html {
            font-family: "Cairo", sans-serif;
        }

        html:lang(en) {
            font-family: "Inter", "Cairo", sans-serif;
        }

        html:lang(ar) {
            font-family: "Noto Sans Arabic", "Cairo", sans-serif;
        }

        body {
            width: calc(var(--receipt-render-width-mm) * 1mm);
            max-width: calc(var(--receipt-render-width-px) * 1px);
            margin: 0 auto;
            color: var(--ink);
            background: var(--paper);
            font-family: inherit;
            font-size: var(--font-base);
            line-height: var(--line-height);
            font-weight: 500;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
            font-variant-numeric: tabular-nums lining-nums;
            text-rendering: geometricPrecision;
        }

        img {
            display: block;
            max-width: 100%;
            height: auto;
            image-rendering: -webkit-optimize-contrast;
        }

        .receipt {
            width: calc(var(--receipt-render-width-mm) * 1mm);
            max-width: calc(var(--receipt-render-width-px) * 1px);
            padding-block: calc(var(--receipt-padding-mm) * 1mm);
            padding-inline: calc(var(--receipt-padding-inline-mm) * 1mm);
            background: var(--paper);
        }

        .receipt-header,
        .receipt-main,
        .receipt-footer,
        .section,
        .subsection,
        .info-card,
        .summary-box,
        .ticket-head,
        .note-box {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .receipt-header > * + *,
        .receipt-main > * + *,
        .receipt-footer > * + * {
            margin-top: var(--section-gap);
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.2mm;
        }

        .stack-tight {
            gap: .7mm;
        }

        .mt-sm {
            margin-top: .8mm;
        }

        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: start;
        }

        .text-end {
            text-align: end;
        }

        .secondary {
            font-size: var(--font-small);
            font-weight: 600;
        }

        .micro {
            font-size: var(--font-micro);
            font-weight: 600;
        }

        .mono {
            font-family: "Courier New", Courier, monospace;
            letter-spacing: .02em;
        }

        .logo {
            margin: 0 auto;
            max-width: 18mm;
            max-height: 18mm;
            object-fit: contain;
            filter: grayscale(1) contrast(200%);
        }

        .brand-name {
            font-size: calc(var(--font-base) + 1px);
            font-weight: 800;
            line-height: 1.3;
            word-break: break-word;
        }

        .document-title {
            margin: 0;
            font-size: var(--font-title);
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .document-number {
            margin-top: 1.2mm;
            padding: 1mm 0;
            border-top: var(--rule);
            border-bottom: var(--rule);
            font-size: calc(var(--font-title) + 1px);
            font-weight: 900;
            line-height: 1.15;
            text-align: center;
            word-break: break-word;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.2mm;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .8mm 1.5mm;
            border: 1.5px solid #000000;
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.1;
            white-space: nowrap;
        }

        .separator {
            margin: 0;
            border: 0;
            border-top: var(--rule-soft);
        }

        .separator-strong {
            border-top: var(--rule);
        }

        .meta-grid {
            display: flex;
            flex-direction: column;
            gap: .9mm;
        }

        .meta-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 2mm;
        }

        .meta-label {
            flex: 0 0 auto;
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.2;
        }

        .meta-value {
            flex: 1 1 auto;
            font-weight: 700;
            text-align: end;
            line-height: 1.25;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .meta-value.meta-value-start {
            text-align: start;
        }

        .section-title {
            margin: 0 0 1.2mm;
            padding-bottom: .7mm;
            border-bottom: var(--rule);
            font-size: var(--font-small);
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .info-card {
            padding-top: 1mm;
            border-top: 1px solid #000000;
        }

        .columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.2mm;
        }

        .receipt--narrow .columns {
            grid-template-columns: 1fr;
        }

        .items-head,
        .item-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 11mm 18mm;
            gap: 1.6mm;
        }

        .receipt--narrow .items-head,
        .receipt--narrow .item-grid {
            grid-template-columns: minmax(0, 1fr) 10mm 15mm;
        }

        .items-head {
            padding-bottom: .9mm;
            border-bottom: var(--rule);
            font-size: var(--font-micro);
            font-weight: 900;
            line-height: 1.15;
        }

        .items {
            display: flex;
            flex-direction: column;
        }

        .item {
            padding: 1.5mm 0;
            border-bottom: var(--rule-soft);
        }

        .item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .item-main {
            min-width: 0;
        }

        .item-name {
            font-weight: 800;
            line-height: 1.22;
            word-break: break-word;
        }

        .item-caption {
            margin-top: .6mm;
            font-size: var(--font-micro);
            font-weight: 600;
            line-height: 1.2;
            word-break: break-word;
        }

        .item-options {
            margin-top: .7mm;
            display: flex;
            flex-direction: column;
            gap: .5mm;
            padding-inline-start: 3mm;
        }

        .item-option {
            font-size: var(--font-micro);
            font-weight: 600;
            line-height: 1.2;
            word-break: break-word;
        }

        .item-qty,
        .item-amount,
        .summary-value,
        .payment-amount {
            white-space: nowrap;
            text-align: end;
            unicode-bidi: plaintext;
        }

        .item-qty {
            font-weight: 700;
        }

        .item-amount {
            font-weight: 900;
        }

        .compact-item-top,
        .compact-item-meta,
        .summary-row,
        .payment-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 2mm;
        }

        .compact-item-meta {
            margin-top: .7mm;
            font-size: var(--font-micro);
            font-weight: 600;
            line-height: 1.2;
        }

        .summary-box {
            padding: 1mm 0;
            border-top: var(--rule);
            border-bottom: var(--rule);
        }

        .summary-row {
            padding: .45mm 0;
        }

        .summary-label {
            font-weight: 700;
            line-height: 1.2;
        }

        .summary-value {
            font-weight: 800;
        }

        .summary-row.total {
            margin-top: .8mm;
            padding-top: 1mm;
            border-top: var(--rule);
            font-size: calc(var(--font-base) + .5px);
        }

        .summary-row.total .summary-label,
        .summary-row.total .summary-value {
            font-weight: 900;
        }

        .summary-row.balance {
            font-size: calc(var(--font-base) + .3px);
        }

        .payment-list {
            display: flex;
            flex-direction: column;
            gap: 1.2mm;
        }

        .payment-method {
            font-weight: 800;
            line-height: 1.2;
        }

        .payment-detail {
            margin-top: .4mm;
            font-size: var(--font-micro);
            font-weight: 600;
            line-height: 1.2;
        }

        .payment-amount {
            font-weight: 800;
        }

        .note-box {
            padding: 1.5mm;
            border: 1.5px solid #000000;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: var(--font-small);
            font-weight: 600;
            line-height: 1.3;
        }

        .bill-sheet {
            padding-bottom: 1.4mm;
            border-bottom: var(--rule);
        }

        .bill-logo {
            margin-bottom: 1mm;
        }

        .bill-ticket-title {
            font-size: calc(var(--font-title) + 2px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: .04em;
            text-align: center;
            text-transform: uppercase;
        }

        .bill-brand {
            margin-top: .8mm;
            font-size: calc(var(--font-base) + .8px);
            font-weight: 800;
            line-height: 1.2;
            text-align: center;
            word-break: break-word;
        }

        .bill-brand-subtitle {
            margin-top: .35mm;
            font-size: var(--font-small);
            font-weight: 600;
            line-height: 1.2;
            text-align: center;
            word-break: break-word;
        }

        .bill-order-number {
            margin-top: 1.1mm;
            padding: 1mm 0;
            border-top: var(--rule);
            border-bottom: var(--rule);
            font-size: calc(var(--font-title) + 4px);
            font-weight: 900;
            line-height: 1;
            text-align: center;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .bill-order-date {
            margin-top: .8mm;
            display: flex;
            flex-direction: column;
            gap: .25mm;
            align-items: center;
            text-align: center;
        }

        .bill-order-date-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .bill-order-date-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.15;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .bill-details-block {
            margin-top: 1.2mm;
            display: flex;
            flex-direction: column;
            gap: .75mm;
            padding-top: 1mm;
            border-top: 1px solid #000000;
        }

        .bill-details-row {
            display: grid;
            grid-template-columns: 20mm minmax(0, 1fr);
            gap: 1.6mm;
            align-items: start;
        }

        .bill-details-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.15;
            text-transform: uppercase;
            unicode-bidi: isolate;
            white-space: nowrap;
        }

        .bill-details-label::after {
            content: ":";
            margin-inline-start: .6mm;
        }

        .bill-details-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.18;
            text-align: end;
            word-break: break-word;
            unicode-bidi: plaintext;
            min-width: 0;
        }

        .bill-notes-section {
            padding-top: 1.2mm;
            border-top: var(--rule);
        }

        .bill-notes-head {
            display: inline-block;
            font-size: var(--font-micro);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .bill-notes-box {
            margin-top: .9mm;
            padding: 1.4mm 1.6mm;
            border: 1px solid #000000;
            border-inline-start: var(--rule);
            white-space: pre-wrap;
            word-break: break-word;
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.32;
            unicode-bidi: plaintext;
        }

        .bill-footer {
            margin-top: 3.2mm;
        }

        .invoice-sheet {
            padding-bottom: 1.4mm;
            border-bottom: var(--rule);
        }

        .invoice-logo {
            margin-bottom: 1mm;
        }

        .invoice-ticket-title {
            font-size: calc(var(--font-title) + 2px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: .04em;
            text-align: center;
            text-transform: uppercase;
        }

        .invoice-brand {
            margin-top: .8mm;
            font-size: calc(var(--font-base) + .8px);
            font-weight: 800;
            line-height: 1.2;
            text-align: center;
            word-break: break-word;
        }

        .invoice-brand-subtitle {
            margin-top: .35mm;
            font-size: var(--font-small);
            font-weight: 600;
            line-height: 1.2;
            text-align: center;
            word-break: break-word;
        }

        .invoice-order-number {
            margin-top: 1.1mm;
            padding: 1mm 0;
            border-top: var(--rule);
            border-bottom: var(--rule);
            font-size: calc(var(--font-title) + 4px);
            font-weight: 900;
            line-height: 1;
            text-align: center;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .invoice-order-date {
            margin-top: .8mm;
            display: flex;
            flex-direction: column;
            gap: .25mm;
            align-items: center;
            text-align: center;
        }

        .invoice-order-date-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .invoice-order-date-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.15;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .invoice-details-block {
            margin-top: 1.2mm;
            display: flex;
            flex-direction: column;
            gap: .75mm;
            padding-top: 1mm;
            border-top: 1px solid #000000;
        }

        .invoice-details-row {
            display: grid;
            grid-template-columns: 20mm minmax(0, 1fr);
            gap: 1.6mm;
            align-items: start;
        }

        .invoice-details-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.15;
            text-transform: uppercase;
            unicode-bidi: isolate;
            white-space: nowrap;
        }

        .invoice-details-label::after {
            content: ":";
            margin-inline-start: .6mm;
        }

        .invoice-details-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.18;
            text-align: end;
            word-break: break-word;
            unicode-bidi: plaintext;
            min-width: 0;
        }

        .invoice-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.2mm;
        }

        .receipt--narrow .invoice-parties {
            grid-template-columns: 1fr;
        }

        .invoice-party-card {
            padding-top: 1mm;
            border-top: 1px solid #000000;
        }

        .invoice-party-title {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .invoice-party-name {
            margin-top: .8mm;
            font-weight: 800;
            line-height: 1.22;
            word-break: break-word;
        }

        .invoice-party-lines {
            margin-top: .7mm;
            display: flex;
            flex-direction: column;
            gap: .4mm;
        }

        .invoice-party-line {
            font-size: var(--font-micro);
            font-weight: 600;
            line-height: 1.2;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .waiter-sheet {
            padding-bottom: 1.4mm;
            border-bottom: var(--rule);
        }

        .waiter-ticket-title {
            font-size: calc(var(--font-title) + 2px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: .04em;
            text-align: center;
            text-transform: uppercase;
        }

        .waiter-order-number {
            margin-top: 1.1mm;
            padding: 1mm 0;
            border-top: var(--rule);
            border-bottom: var(--rule);
            font-size: calc(var(--font-title) + 4px);
            font-weight: 900;
            line-height: 1;
            text-align: center;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .waiter-order-date {
            margin-top: .8mm;
            display: flex;
            flex-direction: column;
            gap: .25mm;
            align-items: center;
            text-align: center;
        }

        .waiter-order-date-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .waiter-order-date-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.15;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .waiter-details-block {
            margin-top: 1.2mm;
            display: flex;
            flex-direction: column;
            gap: .75mm;
            padding-top: 1mm;
            border-top: 1px solid #000000;
        }

        .waiter-details-row {
            display: grid;
            grid-template-columns: 20mm minmax(0, 1fr);
            gap: 1.6mm;
            align-items: start;
        }

        .waiter-details-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.15;
            text-transform: uppercase;
            unicode-bidi: isolate;
            white-space: nowrap;
        }

        .waiter-details-label::after {
            content: ":";
            margin-inline-start: .6mm;
        }

        .waiter-details-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.18;
            text-align: end;
            word-break: break-word;
            unicode-bidi: plaintext;
            min-width: 0;
        }

        .waiter-footer {
            margin-top: 3.4mm;
        }

        .waiter-notes-section {
            padding-top: 1.2mm;
            border-top: var(--rule);
        }

        .waiter-notes-head {
            display: inline-block;
            font-size: var(--font-micro);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .waiter-notes-box {
            margin-top: .9mm;
            padding: 1.4mm 1.6mm;
            border: 1px solid #000000;
            border-inline-start: var(--rule);
            white-space: pre-wrap;
            word-break: break-word;
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.32;
            unicode-bidi: plaintext;
        }

        .kitchen-items {
            display: flex;
            flex-direction: column;
        }

        .kitchen-sheet {
            padding-bottom: 1.4mm;
            border-bottom: var(--rule);
        }

        .kitchen-ticket-title {
            font-size: calc(var(--font-title) + 3px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: .04em;
            text-align: center;
            text-transform: uppercase;
        }

        .kitchen-order-number {
            margin-top: 1.1mm;
            padding: 1mm 0;
            border-top: var(--rule);
            border-bottom: var(--rule);
            font-size: calc(var(--font-title) + 5px);
            font-weight: 900;
            line-height: 1;
            text-align: center;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .kitchen-order-date {
            margin-top: .8mm;
            display: flex;
            flex-direction: column;
            gap: .25mm;
            align-items: center;
            text-align: center;
        }

        .kitchen-order-date-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .kitchen-order-date-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.15;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .kitchen-details-block {
            margin-top: 1.2mm;
            display: flex;
            flex-direction: column;
            gap: .75mm;
            padding-top: 1mm;
            border-top: 1px solid #000000;
        }

        .kitchen-details-block--section {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .kitchen-details-block--section .kitchen-details-row {
            grid-template-columns: 20mm minmax(0, 1fr);
            gap: 1.6mm;
        }

        .kitchen-details-block--section .kitchen-details-label::after {
            content: ":";
            margin-inline-start: .6mm;
        }

        .kitchen-details-block--header .kitchen-details-value {
            text-align: right;
        }

        .kitchen-details-block--header .kitchen-details-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 2mm;
        }

        .kitchen-details-block--header .kitchen-details-label {
            flex: 0 0 auto;
            min-width: 24mm;
        }

        .kitchen-details-block--header .kitchen-details-value {
            flex: 1 1 auto;
            margin-inline-start: auto;
            width: auto;
            text-align: right;
        }

        .kitchen-details-block--header .kitchen-details-label::after {
            content: ":";
            margin-inline-start: .8mm;
        }

        .kitchen-details-row {
            display: grid;
            grid-template-columns: 15mm minmax(0, 1fr);
            gap: 1.2mm;
            align-items: start;
        }

        .kitchen-details-label {
            font-size: var(--font-micro);
            font-weight: 800;
            line-height: 1.15;
            text-transform: uppercase;
            unicode-bidi: isolate;
            white-space: nowrap;
        }

        .kitchen-details-value {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.18;
            word-break: break-word;
            unicode-bidi: plaintext;
            min-width: 0;
        }

        .kitchen-subsection {
            padding-top: 1.1mm;
            border-top: var(--rule);
        }

        .kitchen-notes-panel {
            font-size: var(--font-small);
            font-weight: 700;
            line-height: 1.25;
            white-space: pre-wrap;
            word-break: break-word;
            unicode-bidi: plaintext;
        }

        .kitchen-footer {
            margin-top: 4mm;
        }

        .kitchen-item {
            display: flex;
            align-items: flex-start;
            gap: 1.6mm;
            padding: 1.6mm 0;
            border-bottom: 1px dashed #000000;
        }

        .kitchen-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .kitchen-qty {
            flex: 0 0 10mm;
            padding: .95mm .65mm;
            border: var(--rule);
            font-weight: 900;
            line-height: 1.05;
            text-align: center;
        }

        .kitchen-name {
            font-size: calc(var(--font-base) + .8px);
            font-weight: 900;
            line-height: 1.15;
            word-break: break-word;
        }

        .kitchen-modifiers {
            margin-top: .6mm;
            display: flex;
            flex-direction: column;
            gap: .35mm;
            padding-inline-start: 2.2mm;
        }

        .kitchen-modifier {
            font-size: var(--font-micro);
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
        }

        html[dir="rtl"] .kitchen-order-date,
        html[dir="rtl"] .kitchen-order-date-label,
        html[dir="rtl"] .kitchen-order-date-value,
        html[dir="rtl"] .kitchen-details-label,
        html[dir="rtl"] .kitchen-details-value,
        html[dir="rtl"] .kitchen-notes-panel,
        html[dir="rtl"] .kitchen-name,
        html[dir="rtl"] .kitchen-modifier,
        html[dir="rtl"] .section-title {
            text-align: right;
            direction: rtl;
        }

        html[dir="rtl"] .kitchen-sheet,
        html[dir="rtl"] .kitchen-details-block,
        html[dir="rtl"] .kitchen-subsection,
        html[dir="rtl"] .kitchen-item,
        html[dir="rtl"] .item-main,
        html[dir="rtl"] .kitchen-modifiers,
        html[dir="rtl"] .section-title {
            direction: rtl;
        }

        html[dir="rtl"] .kitchen-details-row {
            grid-template-columns: minmax(0, 1fr) 15mm;
        }

        html[dir="rtl"] .kitchen-details-block--section .kitchen-details-row {
            grid-template-columns: minmax(0, 1fr) 20mm;
        }

        html[dir="rtl"] .kitchen-details-block--section .kitchen-details-label::after {
            margin-inline-start: 0;
            margin-inline-end: .6mm;
        }

        html[dir="rtl"] .kitchen-details-block--header .kitchen-details-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 24mm;
            gap: 2mm;
            align-items: start;
        }

        html[dir="rtl"] .kitchen-details-block--header .kitchen-details-value {
            margin-inline-start: 0;
            text-align: start;
        }

        html[dir="rtl"] .kitchen-details-block--header .kitchen-details-label::after {
            margin-inline-start: 0;
            margin-inline-end: .8mm;
        }

        html[dir="ltr"] .kitchen-order-date,
        html[dir="ltr"] .kitchen-order-date-label,
        html[dir="ltr"] .kitchen-order-date-value,
        html[dir="ltr"] .kitchen-details-label,
        html[dir="ltr"] .kitchen-details-value,
        html[dir="ltr"] .kitchen-notes-panel,
        html[dir="ltr"] .kitchen-name,
        html[dir="ltr"] .kitchen-modifier,
        html[dir="ltr"] .section-title {
            text-align: left;
            direction: ltr;
        }

        html[dir="rtl"] .kitchen-ticket-title,
        html[dir="ltr"] .kitchen-ticket-title {
            text-align: center;
        }

        html[dir="rtl"] .waiter-order-date,
        html[dir="rtl"] .waiter-order-date-label,
        html[dir="rtl"] .waiter-order-date-value,
        html[dir="rtl"] .waiter-details-label,
        html[dir="rtl"] .waiter-details-value,
        html[dir="rtl"] .waiter-notes-head,
        html[dir="rtl"] .waiter-notes-box {
            text-align: right;
            direction: rtl;
        }

        html[dir="rtl"] .waiter-sheet,
        html[dir="rtl"] .waiter-details-block {
            direction: rtl;
        }

        html[dir="rtl"] .waiter-details-row {
            grid-template-columns: minmax(0, 1fr) 20mm;
        }

        html[dir="rtl"] .waiter-details-label::after {
            margin-inline-start: 0;
            margin-inline-end: .6mm;
        }

        html[dir="ltr"] .waiter-order-date,
        html[dir="ltr"] .waiter-order-date-label,
        html[dir="ltr"] .waiter-order-date-value,
        html[dir="ltr"] .waiter-details-label,
        html[dir="ltr"] .waiter-details-value,
        html[dir="ltr"] .waiter-notes-head,
        html[dir="ltr"] .waiter-notes-box {
            text-align: left;
            direction: ltr;
        }

        html[dir="rtl"] .waiter-ticket-title,
        html[dir="ltr"] .waiter-ticket-title,
        html[dir="rtl"] .waiter-order-number,
        html[dir="ltr"] .waiter-order-number {
            text-align: center;
        }

        html[dir="rtl"] .bill-order-date,
        html[dir="rtl"] .bill-order-date-label,
        html[dir="rtl"] .bill-order-date-value,
        html[dir="rtl"] .bill-details-label,
        html[dir="rtl"] .bill-details-value,
        html[dir="rtl"] .bill-notes-head,
        html[dir="rtl"] .bill-notes-box {
            text-align: right;
            direction: rtl;
        }

        html[dir="rtl"] .bill-sheet,
        html[dir="rtl"] .bill-details-block {
            direction: rtl;
        }

        html[dir="rtl"] .bill-details-row {
            grid-template-columns: minmax(0, 1fr) 20mm;
        }

        html[dir="rtl"] .bill-details-label::after {
            margin-inline-start: 0;
            margin-inline-end: .6mm;
        }

        html[dir="ltr"] .bill-order-date,
        html[dir="ltr"] .bill-order-date-label,
        html[dir="ltr"] .bill-order-date-value,
        html[dir="ltr"] .bill-details-label,
        html[dir="ltr"] .bill-details-value,
        html[dir="ltr"] .bill-notes-head,
        html[dir="ltr"] .bill-notes-box {
            text-align: left;
            direction: ltr;
        }

        html[dir="rtl"] .bill-ticket-title,
        html[dir="ltr"] .bill-ticket-title,
        html[dir="rtl"] .bill-brand,
        html[dir="ltr"] .bill-brand,
        html[dir="rtl"] .bill-brand-subtitle,
        html[dir="ltr"] .bill-brand-subtitle,
        html[dir="rtl"] .bill-order-number,
        html[dir="ltr"] .bill-order-number {
            text-align: center;
        }

        html[dir="rtl"] .invoice-order-date,
        html[dir="rtl"] .invoice-order-date-label,
        html[dir="rtl"] .invoice-order-date-value,
        html[dir="rtl"] .invoice-details-label,
        html[dir="rtl"] .invoice-details-value,
        html[dir="rtl"] .invoice-party-title,
        html[dir="rtl"] .invoice-party-name,
        html[dir="rtl"] .invoice-party-line {
            text-align: right;
            direction: rtl;
        }

        html[dir="rtl"] .invoice-sheet,
        html[dir="rtl"] .invoice-details-block,
        html[dir="rtl"] .invoice-parties,
        html[dir="rtl"] .invoice-party-card {
            direction: rtl;
        }

        html[dir="rtl"] .invoice-details-row {
            grid-template-columns: minmax(0, 1fr) 20mm;
        }

        html[dir="rtl"] .invoice-details-label::after {
            margin-inline-start: 0;
            margin-inline-end: .6mm;
        }

        html[dir="ltr"] .invoice-order-date,
        html[dir="ltr"] .invoice-order-date-label,
        html[dir="ltr"] .invoice-order-date-value,
        html[dir="ltr"] .invoice-details-label,
        html[dir="ltr"] .invoice-details-value,
        html[dir="ltr"] .invoice-party-title,
        html[dir="ltr"] .invoice-party-name,
        html[dir="ltr"] .invoice-party-line {
            text-align: left;
            direction: ltr;
        }

        html[dir="rtl"] .invoice-ticket-title,
        html[dir="ltr"] .invoice-ticket-title,
        html[dir="rtl"] .invoice-brand,
        html[dir="ltr"] .invoice-brand,
        html[dir="rtl"] .invoice-brand-subtitle,
        html[dir="ltr"] .invoice-brand-subtitle,
        html[dir="rtl"] .invoice-order-number,
        html[dir="ltr"] .invoice-order-number {
            text-align: center;
        }

        .qrcode {
            width: 22mm;
            margin: 0 auto;
        }

        @media screen {
            body {
                padding: 0;
            }
        }

        @media print {
            html,
            body,
            .receipt {
                width: calc(var(--receipt-render-width-mm) * 1mm);
                max-width: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="receipt {{ $isNarrow ? 'receipt--narrow' : 'receipt--wide' }}" data-paper-width-mm="{{ $paperWidthMm }}"
     data-document-locale="{{ $documentLocale }}" data-document-direction="{{ $documentDirection }}">
    <header class="receipt-header">
        @yield('header')
    </header>

    <main class="receipt-main">
        @yield('content')
    </main>

    <footer class="receipt-footer">
        @yield('footer')
    </footer>
</div>
</body>
</html>
