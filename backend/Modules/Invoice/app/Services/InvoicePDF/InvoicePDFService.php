<?php

namespace Modules\Invoice\Services\InvoicePDF;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Invoice\Models\Invoice;
use Spatie\Browsershot\Browsershot;
use Throwable;

class InvoicePDFService implements InvoicePDFServiceInterface
{
    /** @inheritDoc */
    public function view(string $uuid): Response
    {
        $invoice = $this->getInvoice($uuid);

        $content = $this->getContent($invoice);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    /**
     * Get invoice
     *
     * @param string $uuid
     * @return Invoice
     */
    private function getInvoice(string $uuid): Invoice
    {
        $with = [
            "seller",
            "buyer",
            "discounts",
            "taxes",
            "allocations" => fn($query) => $query->with(["payment"]),
            "lines",
            "branch:id,name",
            "referenceInvoice:id,invoice_number,uuid"
        ];

        $invoice = Invoice::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (is_null($invoice->file_info)) {
            $invoice = Invoice::query()
                ->with($with)
                ->where('uuid', $uuid)
                ->firstOrFail();
        }

        return $invoice;
    }

    /**
     * Get invoice pdf content
     *
     * @param Invoice $invoice
     * @return string
     * @throws Throwable
     */
    private function getContent(Invoice $invoice): string
    {
        if (is_null($invoice->file_info)) {
            $content = $this->saveInvoiceFile($invoice);
        } else {
            $content = Storage::disk($invoice->file_info['disk'])->get($invoice->file_info['path']);
        }

        return $content;
    }

    /**
     * Store invoice as pdf file
     *
     * @param Invoice $invoice
     * @return string
     * @throws Throwable
     */
    private function saveInvoiceFile(Invoice $invoice): string
    {
        $html = view('invoices.pdf', compact('invoice'))->render();

        $pdfContent = Browsershot::html($html)
            ->format('A4')
            ->margins(5, 5, 5, 5)
            ->pdf();

        $path = "invoices/$invoice->invoice_number.pdf";
        $disk = 'local';

        if (Storage::disk($disk)->put($path, $pdfContent)) {
            $invoice->update(['file_info' => ["disk" => $disk, "path" => $path]]);
        }

        return $pdfContent;
    }

    /** @inheritDoc */
    public function download(string $uuid): Response
    {
        $invoice = $this->getInvoice($uuid);

        $content = $this->getContent($invoice);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf',
        ]);
    }
}
