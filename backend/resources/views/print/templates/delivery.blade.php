@extends('print.layout')

@php
    $isNarrow = (int)($profile['paper_width_mm'] ?? 80) <= 58;

    $order = (array)data_get($payload, 'order', []);
    $branch = (array)data_get($payload, 'branch', []);
    $customer = (array)data_get($payload, 'customer', []);
    $discount = data_get($payload, 'discount');
    $products = collect(data_get($payload, 'products', []));
    $taxes = collect(data_get($payload, 'taxes', []));
    $payments = collect(data_get($payload, 'payments', []));

    $currencySubunit = (int)data_get($payload, 'currency_subunit', 2);
    $fmt = fn($n) => number_format((float)($n ?? 0), $currencySubunit, '.', '');
    $fmtQty = function ($n): string {
        $value = number_format((float)($n ?? 0), 3, '.', '');

        return rtrim(rtrim($value, '0'), '.') ?: '0';
    };

    $defaultLocale = (string)setting('default_locale', config('app.locale'));
    $t = fn(string $key) => __("printer::thermal_print.$key", [], $defaultLocale);

    $logo = data_get($branch, 'logo');
    $branchName = data_get($branch, 'name') ?: data_get($branch, 'legal_name');
    $orderNumber = data_get($order, 'order_number') ?: data_get($order, 'reference_no') ?: '---';
    $branchContact = collect([
        data_get($branch, 'phone'),
        data_get($branch, 'email'),
    ])->filter()->implode(' | ');
@endphp

@section('title', 'Delivery')

@section('header')
    @if(!empty($logo))
        <img class="logo" src="{{ $logo }}" alt="{{ $branchName ?: 'Logo' }}">
    @endif

    <section class="section text-center stack">
        @if($branchName)
            <div class="brand-name">{{ $branchName }}</div>
        @endif

        @if(data_get($branch, 'address_line1'))
            <div class="secondary">{{ data_get($branch, 'address_line1') }}</div>
        @endif

        @if($branchContact !== '')
            <div class="secondary">{{ $branchContact }}</div>
        @endif
    </section>

    <section class="ticket-head stack-tight">
        <div class="chip-row">
            <span class="chip">{{ $t('delivery') }}</span>
            @if(data_get($order, 'type'))
                <span class="chip">{{ data_get($order, 'type') }}</span>
            @endif
        </div>

        <div class="document-number">#{{ $orderNumber }}</div>

        @if(data_get($order, 'scheduled_at'))
            <div class="secondary text-center">{{ data_get($order, 'scheduled_at') }}</div>
        @elseif(data_get($order, 'order_date'))
            <div class="secondary text-center">{{ data_get($order, 'order_date') }}</div>
        @endif
    </section>

    <section class="section">
        <div class="meta-grid">
            <div class="meta-row">
                <div class="meta-label">{{ $t('customer') }}</div>
                <div class="meta-value">{{ data_get($customer, 'name') ?: '---' }}</div>
            </div>

            @if(data_get($customer, 'phone'))
                <div class="meta-row">
                    <div class="meta-label">{{ $t('phone') }}</div>
                    <div class="meta-value">{{ data_get($customer, 'phone') }}</div>
                </div>
            @endif

            @if(data_get($customer, 'email'))
                <div class="meta-row">
                    <div class="meta-label">{{ $t('email') }}</div>
                    <div class="meta-value">{{ data_get($customer, 'email') }}</div>
                </div>
            @endif

            @if(data_get($order, 'car_plate') || data_get($order, 'car_description'))
                <div class="meta-row">
                    <div class="meta-label">{{ $t('vehicle') }}</div>
                    <div class="meta-value">
                        {{ trim(implode(' | ', array_filter([data_get($order, 'car_plate'), data_get($order, 'car_description')]))) }}
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('content')
    <section class="section">
        <h2 class="section-title">{{ $t('items') }}</h2>

        @if(!$isNarrow)
            <div class="items-head">
                <div>{{ $t('description') }}</div>
                <div class="text-end">{{ $t('qty') }}</div>
                <div class="text-end">{{ $t('amount') }}</div>
            </div>
        @endif

        <div class="items">
            @foreach($products as $product)
                @php
                    $product = (array)$product;
                    $options = collect(data_get($product, 'options', []));
                    $qty = (float)data_get($product, 'quantity', 0);
                    $unitPrice = (float)data_get($product, 'unit_price', 0);
                    $lineTotal = (float)(data_get($product, 'total') ?? data_get($product, 'subtotal') ?? 0);
                @endphp

                <article class="item">
                    @if($isNarrow)
                        <div class="compact-item-top">
                            <div class="item-main">
                                <div class="item-name">{{ data_get($product, 'name') }}</div>
                            </div>
                            <div class="item-amount">{{ $fmt($lineTotal) }}</div>
                        </div>

                        <div class="compact-item-meta">
                            <div>{{ $fmtQty($qty) }} x {{ $fmt($unitPrice) }}</div>
                        </div>
                    @else
                        <div class="item-grid">
                            <div class="item-main">
                                <div class="item-name">{{ data_get($product, 'name') }}</div>
                                <div class="item-caption">{{ $t('unit') }}: {{ $fmt($unitPrice) }}</div>
                            </div>
                            <div class="item-qty">{{ $fmtQty($qty) }}</div>
                            <div class="item-amount">{{ $fmt($lineTotal) }}</div>
                        </div>
                    @endif

                    @if($options->isNotEmpty())
                        <div class="item-options">
                            @foreach($options as $option)
                                @php
                                    $option = (array)$option;
                                    $values = collect(data_get($option, 'values', []));
                                    $labels = $values->pluck('label')->filter()->implode(', ');
                                    $optionPrice = (float)$values->sum('price');
                                @endphp
                                <div class="item-option">
                                    + {{ data_get($option, 'name') }}@if($labels !== ''): {{ $labels }}@endif
                                    @if($optionPrice > 0)
                                        | {{ $fmt($optionPrice) }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">{{ $t('summary') }}</h2>

        <div class="summary-box">
            @if(!is_null(data_get($order, 'subtotal')))
                <div class="summary-row">
                    <div class="summary-label">{{ $t('subtotal') }}</div>
                    <div class="summary-value">{{ $fmt(data_get($order, 'subtotal')) }}</div>
                </div>
            @endif

            @if(!empty($discount))
                <div class="summary-row">
                    <div class="summary-label">
                        {{ $t('discount') }}
                        @if(data_get($discount, 'name'))
                            ({{ data_get($discount, 'name') }})
                        @elseif(data_get($discount, 'discount'))
                            ({{ data_get($discount, 'discount') }})
                        @endif
                    </div>
                    <div class="summary-value">-{{ $fmt(data_get($discount, 'amount')) }}</div>
                </div>
            @endif

            @foreach($taxes as $tax)
                <div class="summary-row">
                    <div class="summary-label">{{ data_get($tax, 'name') }}</div>
                    <div class="summary-value">{{ $fmt(data_get($tax, 'amount')) }}</div>
                </div>
            @endforeach

            @if(!is_null(data_get($order, 'total')))
                <div class="summary-row total">
                    <div class="summary-label">{{ $t('total') }}</div>
                    <div class="summary-value">{{ $fmt(data_get($order, 'total')) }}</div>
                </div>
            @endif

            @if(!is_null(data_get($order, 'due_amount')) && (float)data_get($order, 'due_amount') > 0)
                <div class="summary-row balance">
                    <div class="summary-label">{{ $t('due') }}</div>
                    <div class="summary-value">{{ $fmt(data_get($order, 'due_amount')) }}</div>
                </div>
            @endif
        </div>
    </section>

    @if($payments->isNotEmpty())
        <section class="section">
            <h2 class="section-title">{{ $t('payments') }}</h2>

            <div class="payment-list">
                @foreach($payments as $payment)
                    @php
                        $transactionId = (string)data_get($payment, 'transaction_id', '');
                    @endphp

                    <div class="payment-row">
                        <div>
                            <div class="payment-method">{{ data_get($payment, 'method') }}</div>
                            @if($transactionId !== '')
                                <div class="payment-detail mono">
                                    {{ $t('transaction') }}: {{ $transactionId }}
                                </div>
                            @endif
                        </div>

                        <div class="payment-amount">{{ $fmt(data_get($payment, 'amount')) }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@section('footer')
    @if(data_get($order, 'notes'))
        <section class="section">
            <h2 class="section-title">{{ $t('notes') }}</h2>
            <div class="note-box">{{ data_get($order, 'notes') }}</div>
        </section>
    @endif
@endsection
