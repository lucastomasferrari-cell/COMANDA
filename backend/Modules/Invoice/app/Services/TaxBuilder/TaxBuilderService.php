<?php

namespace Modules\Invoice\Services\TaxBuilder;


use Illuminate\Support\Collection;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Models\InvoiceLine;
use Modules\Invoice\Models\InvoiceTax;

class TaxBuilderService implements TaxBuilderServiceInterface
{
    /** @inheritDoc */
    public function createLineTaxes(Invoice $invoice, InvoiceLine $line, Collection $taxes): void
    {
        foreach ($taxes as $tax) {
            InvoiceTax::query()
                ->create([
                    'name' => $tax->getTranslations('name'),
                    'rate' => $tax->rate,
                    'currency' => $tax->currency,
                    'currency_rate' => $tax->currency_rate,
                    'amount' => $tax->amount->amount(),
                    'type' => $tax->type,
                    'compound' => $tax->compound,
                    'invoice_id' => $invoice->id,
                    'invoice_line_id' => $line->id,
                    'tax_id' => $tax->tax_id,
                    'code' => $tax->tax->code,
                ]);
        }

    }

    /** @inheritDoc */
    public function createInvoiceTaxes(Invoice $invoice, Collection $orders): void
    {
        $grouped = collect();

        foreach ($orders as $order) {
            foreach ($order->taxes as $orderTax) {
                $key = $orderTax->tax_id . '-' . $orderTax->tax->code;

                $current = $grouped->get($key, [
                    'orderTax' => $orderTax,
                    'amount' => 0,
                ]);

                $current['amount'] += $orderTax->amount->amount();

                $grouped->put($key, $current);
            }
        }

        foreach ($grouped as $item) {
            $orderTax = $item['orderTax'];

            InvoiceTax::query()->create([
                'invoice_id' => $invoice->id,
                'invoice_line_id' => null,
                'tax_id' => $orderTax->tax_id,
                'code' => $orderTax->tax->code,
                'name' => $orderTax->getTranslations('name'),
                'rate' => $orderTax->rate,
                'currency' => $orderTax->currency,
                'currency_rate' => $orderTax->currency_rate,
                'amount' => $item['amount'],
                'type' => $orderTax->type,
                'compound' => $orderTax->compound,
            ]);
        }
    }
}
