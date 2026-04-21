@extends('print.layout')

@php
    $isNarrow = (int)($profile['paper_width_mm'] ?? 80) <= 58;

    $fmt = fn($n) => number_format((float)($n ?? 0), data_get($payload, 'currency_subunit', 2), '.', '');
    $fmtQty = function ($n): string {
        $value = number_format((float)($n ?? 0), 3, '.', '');

        return rtrim(rtrim($value, '0'), '.') ?: '0';
    };

    $branch = (array)data_get($payload, 'branch', []);
    $seller = (array)data_get($payload, 'seller', []);
    $buyer = (array)data_get($payload, 'buyer', []);
    $issuedAt = (array)data_get($payload, 'issued_at', []);

    $lines = collect(data_get($payload, 'lines', []));
    $taxes = collect(data_get($payload, 'taxes', []));
    $discounts = collect(data_get($payload, 'discounts', []));
    $allocations = collect(data_get($payload, 'allocations', []));

    $logo = data_get($branch, 'logo');
    $qrcode = data_get($payload, 'qrcode');

    $defaultLocale = (string)setting('default_locale', config('app.locale'));
    $t = fn(string $key) => __("printer::thermal_print.$key", [], $defaultLocale);

    $branchName = data_get($branch, 'name');
    $invoiceNumber = data_get($payload, 'invoice_number') ?: '---';
    $sellerName = data_get($seller, 'legal_name') ?: $branchName ?: '---';
    $buyerName = data_get($buyer, 'legal_name') ?: '---';
    $issuedAtFullDate = data_get($issuedAt, 'full_date');
    $invoiceType = data_get($payload, 'type');

    $partyLines = function (array $party): array {
        return array_values(array_filter([
            data_get($party, 'vat_tin') ? 'TIN: ' . data_get($party, 'vat_tin') : null,
            data_get($party, 'cr_number') ? 'CR: ' . data_get($party, 'cr_number') : null,
            data_get($party, 'address_line1'),
            data_get($party, 'address_line2'),
            trim(implode(' | ', array_filter([
                data_get($party, 'city'),
                data_get($party, 'state'),
                data_get($party, 'country.name'),
            ]))),
            data_get($party, 'postal_code'),
            data_get($party, 'phone'),
            data_get($party, 'email'),
        ]));
    };

    $headerMetaRows = collect([
        ['label' => $t('description'), 'value' => $invoiceType],
    ])->filter(fn(array $row) => filled($row['value']))->values();

    $sellerPartyLines = $partyLines($seller);
    $buyerPartyLines = $partyLines($buyer);
@endphp

@section('title', 'Invoice')

@section('header')
    <section class="section invoice-sheet">
        @if(!empty($logo))
            <img class="logo invoice-logo" src="{{ $logo }}" alt="{{ $branchName ?: 'Logo' }}">
        @endif

        <div class="invoice-ticket-title">{{ $t('invoice') }}</div>

        @if($sellerName !== '---')
            <div class="invoice-brand">{{ $sellerName }}</div>
        @endif

        @if($branchName && $branchName !== $sellerName)
            <div class="invoice-brand-subtitle">{{ $branchName }}</div>
        @endif

        <div class="invoice-order-number">#{{ $invoiceNumber }}</div>

        @if(filled($issuedAtFullDate))
            <div class="invoice-order-date">
                <span class="invoice-order-date-label">{{ $t('order_date') }}</span>
                <span class="invoice-order-date-value">{{ $issuedAtFullDate }}</span>
            </div>
        @endif

        @if($headerMetaRows->isNotEmpty())
            <div class="invoice-details-block">
                @foreach($headerMetaRows as $row)
                    <div class="invoice-details-row">
                        <div class="invoice-details-label">{{ $row['label'] }}</div>
                        <div class="invoice-details-value">{{ $row['value'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@section('content')
    <section class="section">
        <div class="invoice-parties">
            <div class="invoice-party-card">
                <div class="invoice-party-title">{{ $t('seller') }}</div>
                <div class="invoice-party-name">{{ $sellerName }}</div>
                @if(!empty($sellerPartyLines))
                    <div class="invoice-party-lines">
                        @foreach($sellerPartyLines as $line)
                            <div class="invoice-party-line">{{ $line }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="invoice-party-card">
                <div class="invoice-party-title">{{ $t('buyer') }}</div>
                <div class="invoice-party-name">{{ $buyerName }}</div>
                @if(!empty($buyerPartyLines))
                    <div class="invoice-party-lines">
                        @foreach($buyerPartyLines as $line)
                            <div class="invoice-party-line">{{ $line }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">{{ $t('items') }}</h2>

        @if($lines->isEmpty())
            <div class="secondary">{{ $t('no_items') }}</div>
        @else
            @if(!$isNarrow)
                <div class="items-head">
                    <div>{{ $t('description') }}</div>
                    <div class="text-end">{{ $t('qty') }}</div>
                    <div class="text-end">{{ $t('amount') }}</div>
                </div>
            @endif

            <div class="items">
                @foreach($lines as $line)
                    @php
                        $line = (array)$line;
                        $qty = (float)data_get($line, 'quantity', 0);
                        $unitPrice = (float)data_get($line, 'unit_price', 0);
                        $taxAmount = (float)data_get($line, 'tax_amount', 0);
                        $lineTotal = (float)(data_get($line, 'line_total_incl_tax') ?? data_get($line, 'line_total') ?? 0);
                        $description = data_get($line, 'description') ?: '---';
                    @endphp

                    <article class="item">
                        @if($isNarrow)
                            <div class="compact-item-top">
                                <div class="item-main">
                                    <div class="item-name">{{ $description }}</div>
                                    @if(data_get($line, 'sku'))
                                        <div class="item-caption">SKU: {{ data_get($line, 'sku') }}</div>
                                    @endif
                                </div>
                                <div class="item-amount">{{ $fmt($lineTotal) }}</div>
                            </div>

                            <div class="compact-item-meta">
                                <div>{{ $fmtQty($qty) }} x {{ $fmt($unitPrice) }}</div>
                                @if($taxAmount > 0)
                                    <div>{{ $t('tax') }} {{ $fmt($taxAmount) }}</div>
                                @endif
                            </div>
                        @else
                            <div class="item-grid">
                                <div class="item-main">
                                    <div class="item-name">{{ $description }}</div>
                                    <div class="item-caption">
                                        @if(data_get($line, 'sku'))
                                            SKU: {{ data_get($line, 'sku') }} |
                                        @endif
                                        {{ $t('unit') }}: {{ $fmt($unitPrice) }}
                                        @if($taxAmount > 0)
                                            | {{ $t('tax') }}: {{ $fmt($taxAmount) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="item-qty">{{ $fmtQty($qty) }}</div>
                                <div class="item-amount">{{ $fmt($lineTotal) }}</div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="section">
        <h2 class="section-title" style="border-bottom: none">{{ $t('summary') }}</h2>

        <div class="summary-box">
            <div class="summary-row">
                <div class="summary-label">{{ $t('subtotal') }}</div>
                <div class="summary-value">{{ $fmt(data_get($payload, 'subtotal')) }}</div>
            </div>

            @foreach($discounts as $discount)
                <div class="summary-row">
                    <div class="summary-label">
                        {{ $t('discount') }}
                        @if(data_get($discount, 'name'))
                            ({{ data_get($discount, 'name') }})
                        @endif
                    </div>
                    <div class="summary-value">-{{ $fmt(data_get($discount, 'amount')) }}</div>
                </div>
            @endforeach

            @foreach($taxes as $tax)
                <div class="summary-row">
                    <div class="summary-label">{{ data_get($tax, 'name') }}</div>
                    <div class="summary-value">{{ $fmt(data_get($tax, 'amount')) }}</div>
                </div>
            @endforeach

            <div class="summary-row total">
                <div class="summary-label">{{ $t('grand_total') }}</div>
                <div class="summary-value">{{ $fmt(data_get($payload, 'total')) }}</div>
            </div>

            <div class="summary-row">
                <div class="summary-label">{{ $t('paid') }}</div>
                <div class="summary-value">{{ $fmt(data_get($payload, 'paid_amount')) }}</div>
            </div>

            @if((float)data_get($payload, 'refunded_amount', 0) > 0)
                <div class="summary-row">
                    <div class="summary-label">{{ $t('refunded') }}</div>
                    <div class="summary-value">-{{ $fmt(data_get($payload, 'refunded_amount')) }}</div>
                </div>
            @endif

            <div class="summary-row balance">
                <div class="summary-label">{{ $t('net_paid') }}</div>
                <div class="summary-value">{{ $fmt(data_get($payload, 'net_paid')) }}</div>
            </div>
        </div>
    </section>

    @if($allocations->isNotEmpty())
        <section class="section">
            <h2 class="section-title">{{ $t('payments') }}</h2>

            <div class="payment-list">
                @foreach($allocations as $allocation)
                    @php
                        $paymentMethod = (string)data_get($allocation, 'payment.method', '');
                        $paymentType = strtolower((string)data_get($allocation, 'payment.type', ''));
                        $transactionId = (string)data_get($allocation, 'payment.transaction_id', '');
                    @endphp

                    <div class="payment-row">
                        <div>
                            <div class="payment-method">
                                {{ $paymentMethod }}
                                @if(str_contains($paymentType, 'refund'))
                                    ({{ $t('refund') }})
                                @endif
                            </div>

                            @if($transactionId !== '')
                                <div class="payment-detail mono">
                                    {{ $t('transaction') }}: {{ $transactionId }}
                                </div>
                            @endif
                        </div>

                        <div class="payment-amount">{{ $fmt(data_get($allocation, 'amount')) }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@section('footer')
    @if(!empty($qrcode))
        <section class="section text-center stack-tight">
            <img class="qrcode" src="data:image/png;base64,{{ $qrcode }}" alt="{{ $t('qr_code') }}">
        </section>
    @endif
@endsection
