<?php

namespace Modules\GiftCard\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\GiftCard\Models\GiftCard;

/** @mixin GiftCard */
class GiftCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'code' => $this->code,
            'branch' => [
                'id' => $this->branch_id,
                'name' => $this->relationLoaded('branch') ? $this->branch?->name : null,
            ],
            'batch' => [
                'id' => $this->gift_card_batch_id,
                'name' => $this->relationLoaded('batch') ? $this->batch?->name : null,
            ],
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->relationLoaded('customer') ? $this->customer?->name : null,
            ],
            'scope' => $this->scope->toTrans(),
            'status' => $this->status->toTrans(),
            'initial_balance' => $this->initial_balance,
            'current_balance' => $this->current_balance,
            'currency' => $this->currency,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => dateTimeFormat($this->created_at),
            'updated_at' => dateTimeFormat($this->updated_at),
        ];
    }
}
