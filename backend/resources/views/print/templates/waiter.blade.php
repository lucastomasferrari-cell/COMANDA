@extends('print.layout')

@php
    $isNarrow = (int)($profile['paper_width_mm'] ?? 80) <= 58;

    $order = (array)data_get($payload, 'order', []);
    $customer = (array)data_get($payload, 'customer', []);
    $table = data_get($payload, 'table');
    $discount = data_get($payload, 'discount');
    $products = collect(data_get($payload, 'products', []));
    $taxes = collect(data_get($payload, 'taxes', []));

    $currencySubunit = (int)data_get($payload, 'currency_subunit', 2);
    $fmt = fn($n) => number_format((float)($n ?? 0), $currencySubunit, '.', '');
    $fmtQty = function ($n): string {
        $value = number_format((float)($n ?? 0), 3, '.', '');

        return rtrim(rtrim($value, '0'), '.') ?: '0';
    };

    $defaultLocale = (string)setting('default_locale', config('app.locale'));
    $t = fn(string $key) => __("printer::thermal_print.$key", [], $defaultLocale);

    $orderNumber = data_get($order, 'order_number') ?: data_get($order, 'reference_no') ?: '---';
    $orderDate = data_get($order, 'order_date');
    $scheduledAt = data_get($order, 'scheduled_at');
    $tableName = !empty($table) ? data_get($table, 'name') : null;
    $customerName = data_get($customer, 'name');
    $notes = trim((string)data_get($order, 'notes', ''));

    $topMetaRows = collect([
        ['label' => $t('order_type'), 'value' => data_get($order, 'type')],
        ['label' => $t('scheduled_at'), 'value' => $scheduledAt],
        ['label' => $t('table'), 'value' => $tableName],
        ['label' => $t('customer'), 'value' => $customerName],
    ])->filter(fn(array $row) => filled($row['value']))->values();
@endphp

@section('title', 'Waiter Copy')

@section('header')
    <section class="section waiter-sheet">
        <div class="waiter-ticket-title">{{ $t('waiter_copy') }}</div>

        <div class="waiter-order-number">#{{ $orderNumber }}</div>

        @if(filled($orderDate))
            <div class="waiter-order-date">
                <span class="waiter-order-date-label">{{ $t('order_date') }}</span>
                <span class="waiter-order-date-value">{{ $orderDate }}</span>
            </div>
        @endif

        @if($topMetaRows->isNotEmpty())
            <div class="waiter-details-block">
                @foreach($topMetaRows as $row)
                    <div class="waiter-details-row">
                        <div class="waiter-details-label">{{ $row['label'] }}</div>
                        <div class="waiter-details-value">{{ $row['value'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
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

        @if($products->isEmpty())
            <div class="secondary">{{ $t('no_items') }}</div>
        @else
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
                                        + {{ data_get($option, 'name') }}@if($labels !== '')
                                            : {{ $labels }}
                                        @endif
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
        @endif
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
@endsection

@section('footer')
    @if($notes !== '')
        <section class="section kitchen-subsection">
            <h2 class="section-title">{{ $t('notes') }}</h2>
            <div class="kitchen-notes-panel">{{ $notes }}</div>
        </section>
    @endif

    <section class="section text-center secondary waiter-footer">
        {{ $t('waiter_copy') }} | #{{ $orderNumber }}
    </section>
@endsection
