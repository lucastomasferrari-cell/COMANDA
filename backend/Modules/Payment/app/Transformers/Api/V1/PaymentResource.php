<?php

namespace Modules\Payment\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payment\Models\Payment;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "order_reference_no" => $this->order_reference_no,
            "branch" => [
                "id" => $this->branch_id,
                "name" => $this->relationLoaded("branch") ? $this->branch?->name : "",
            ],
            "cashier" => [
                "id" => $this->cashier_id,
                "name" => $this->relationLoaded("cashier") ? $this->cashier?->name : "",
            ],
            "method" => $this->method->toTrans(),
            "amount" => $this->amount->withConvertedDefaultCurrency($this->currency_rate),
            "created_at" => dateTimeFormat($this->created_at),
        ];
    }
}
