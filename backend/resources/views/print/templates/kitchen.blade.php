@extends('print.layout')

@php
    $order = (array)data_get($payload, 'order', []);
    $waiter = data_get($payload, 'waiter');
    $table = data_get($payload, 'table');

    $productsPayload = data_get($payload, 'products', []);

    if (is_array($productsPayload) && array_key_exists('products', $productsPayload)) {
        $products = collect(data_get($productsPayload, 'products', []));
    } else {
        $products = collect($productsPayload);
    }

    $defaultLocale = (string)setting('default_locale', config('app.locale'));
    $t = fn(string $key) => __("printer::thermal_print.$key", [], $defaultLocale);

    $orderNumber = data_get($order, 'order_number') ?: data_get($order, 'reference_no');
    $orderDate = data_get($order, 'order_date');
    $scheduledAt = data_get($order, 'scheduled_at');
    $waiterName = data_get($waiter, 'name');
    $tableName = !empty($table) ? data_get($table, 'name') : null;
    $notes = trim((string)data_get($order, 'notes', ''));

    $topMetaRows = collect([
        ['label' => $t('order_type'), 'value' => data_get($order, 'type')],
        ['label' => $t('scheduled_at'), 'value' => $scheduledAt],
        ['label' => $t('table'), 'value' => $tableName],
        ['label' => $t('waiter'), 'value' => $waiterName],
    ])->filter(fn(array $row) => filled($row['value']))->values();

    $carDetailsRows = collect([
        ['label' => $t('car_plate'), 'value' => data_get($order, 'car_plate')],
        ['label' => $t('car_description'), 'value' => data_get($order, 'car_description')],
    ])->filter(fn(array $row) => filled($row['value']))->values();
@endphp

@section('title', 'Kitchen Ticket')

@section('header')
    <section class="section kitchen-sheet">
        <div class="kitchen-ticket-title">{{ $t('kitchen_ticket') }}</div>

        @if(filled($orderNumber))
            <div class="kitchen-order-number">#{{ $orderNumber }}</div>
        @endif

        @if(filled($orderDate))
            <div class="kitchen-order-date">
                <span class="kitchen-order-date-label">{{ $t('order_date') }}</span>
                <span class="kitchen-order-date-value">{{ $orderDate }}</span>
            </div>
        @endif

        @if($topMetaRows->isNotEmpty())
            <div class="kitchen-details-block kitchen-details-block--header">
                @foreach($topMetaRows as $row)
                    <div class="kitchen-details-row">
                        <div class="kitchen-details-label">{{ $row['label'] }}</div>
                        <div class="kitchen-details-value">{{ $row['value'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@section('content')
    <section class="section">
        <h2 class="section-title">{{ $t('items') }}</h2>

        @if($products->isEmpty())
            <div class="secondary">{{ $t('no_items') }}</div>
        @else
            <div class="kitchen-items">
                @foreach($products as $product)
                    @php
                        $product = (array)$product;
                        $options = collect(data_get($product, 'options', []));
                    @endphp

                    <article class="kitchen-item">
                        <div class="kitchen-qty">x{{ (int)data_get($product, 'quantity', 0) }}</div>

                        <div class="item-main">
                            <div class="kitchen-name">{{ data_get($product, 'name') }}</div>

                            @if($options->isNotEmpty())
                                <div class="kitchen-modifiers">
                                    @foreach($options as $option)
                                        @php
                                            $option = (array)$option;
                                            $values = collect(data_get($option, 'values', []))->pluck('label')->filter()->implode(', ');
                                        @endphp
                                        <div class="kitchen-modifier">
                                            - {{ data_get($option, 'name') }}@if($values !== '')
                                                : {{ $values }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if($carDetailsRows->isNotEmpty())
        <section class="section kitchen-subsection">
            <h2 class="section-title">{{ $t('car_details') }}</h2>
            <div class="kitchen-details-block kitchen-details-block--section">
                @foreach($carDetailsRows as $row)
                    <div class="kitchen-details-row">
                        <div class="kitchen-details-label">{{ $row['label'] }}</div>
                        <div class="kitchen-details-value">{{ $row['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($notes))
        <section class="section kitchen-subsection">
            <h2 class="section-title">{{ $t('notes') }}</h2>
            <div class="kitchen-notes-panel">{{ $notes }}</div>
        </section>
    @endif
@endsection

@section('footer')
    <section class="section text-center secondary kitchen-footer">
        {{ $t('kitchen_copy') }}@if(filled($orderNumber))
            | #{{ $orderNumber }}
        @endif
    </section>
@endsection
