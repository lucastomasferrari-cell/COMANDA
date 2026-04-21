<?php

namespace Modules\GiftCard\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\GiftCard\Models\GiftCardTransaction;

/** @mixin GiftCardTransaction */
class GiftCardTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'gift_card' => [
                'id' => $this->gift_card_id,
                'code' => $this->relationLoaded('giftCard') ? $this->giftCard?->code : null,
            ],
            'type' => $this->type->toTrans(),
            'amount' => $this->amount->withConvertedDefaultCurrency($this->exchange_rate ?: 1),
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'order_currency' => $this->order_currency,
            'amount_in_order_currency' => $this->amount_in_order_currency,
            'branch' => [
                'id' => $this->branch_id,
                'name' => $this->relationLoaded('branch') ? $this->branch?->name : null,
            ],
            'order' => [
                'id' => $this->order_id,
                'reference_no' => $this->relationLoaded('order') ? $this->order?->reference_no : null,
            ],
            'createdBy' => [
                'id' => $this->created_by,
                'name' => $this->relationLoaded('createdBy') ? $this->createdBy?->name : null,
            ],
            'notes' => $this->notes,
            'transaction_at' => dateTimeFormat($this->transaction_at),
            'created_at' => dateTimeFormat($this->created_at),
        ];
    }
}
