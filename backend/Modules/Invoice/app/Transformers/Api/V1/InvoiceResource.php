<?php

namespace Modules\Invoice\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Invoice\Models\Invoice;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "invoice_number" => $this->invoice_number,
            "uuid" => $this->uuid,
            "reference_invoice" => ReferenceInvoiceResource::make($this->whenLoaded('referenceInvoice')),
            "branch" => [
                "id" => $this->branch_id,
                "name" => $this->relationLoaded("branch") ? $this->branch?->name : "",
            ],
            "seller" => [
                "id" => $this->seller_party_id,
                "name" => $this->relationLoaded("seller") ? $this->seller?->legal_name : null,
            ],
            "buyer" => [
                "id" => $this->buyer_party_id,
                "name" => $this->relationLoaded("buyer") ? $this->buyer?->legal_name : null,
            ],
            "type" => $this->type->toTrans(),
            "status" => $this->status->toTrans(),
            "purpose" => $this->purpose->toTrans(),
            "invoice_kind" => $this->invoice_kind->toTrans(),
            "total" => $this->total->withConvertedDefaultCurrency($this->currency_rate),
            "issued_at" => dateTimeFormat($this->issued_at),
            "download_url" => $this->getDownloadUrl(),
            "pdf_url" => $this->getPDFUrl(),
        ];
    }
}
