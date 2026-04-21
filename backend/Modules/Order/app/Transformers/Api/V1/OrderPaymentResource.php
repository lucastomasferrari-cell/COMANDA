<?php

namespace Modules\Order\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payment\Models\Payment;

/** @mixin Payment */
class OrderPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "transaction_id" => $this->transaction_id,
            "method" => $this->method->toTrans(),
            "type" => $this->type->toTrans(),
            "amount" => $this->amount->withConvertedDefaultCurrency($this->currency_rate),
            "created_at" => dateTimeFormat($this->created_at)
        ];
    }
}
