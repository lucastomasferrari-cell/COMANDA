<?php

namespace Modules\Order\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\Models\Order;

/** @mixin Order */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "branch" => [
                "id" => $this->branch_id,
                "name" => $this->relationLoaded("branch") ? $this->branch?->name : "",
            ],
            "customer" => OrderCustomerResource::make($this->whenLoaded('customer')),
            "reference_no" => $this->reference_no,
            "order_number" => $this->order_number,
            "type" => $this->type->toTrans(),
            "status" => $this->status->toTrans(),
            "payment_status" => $this->payment_status->toTrans(),
            "allow_refund" => $this->refundIsAllowed(),
            "allow_cancel" => $this->cancelIsAllowed(),
            "allow_edit" => $this->editIsAllowed(),
            "total" => $this->total->withConvertedDefaultCurrency($this->currency_rate),
            "due_amount" => $this->due_amount->withConvertedDefaultCurrency($this->currency_rate),
            "refunded_amount" => $this->getRefundedAmount(),
            "created_at" => dateTimeFormat($this->created_at),
        ];
    }
}
