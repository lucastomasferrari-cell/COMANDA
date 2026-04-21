<?php

namespace Modules\GiftCard\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\GiftCard\Models\GiftCardBatch;

/** @mixin GiftCardBatch */
class GiftCardBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cardsGenerated = (int) ($this->cards_generated ?? 0);
        $cardsUsed = (int) ($this->cards_used ?? 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'prefix' => $this->prefix,
            'quantity' => $this->quantity,
            'value' => $this->value,
            'currency' => $this->currency,
            'branch' => [
                'id' => $this->branch_id,
                'name' => $this->relationLoaded('branch') ? $this->branch?->name : null,
            ],
            'cards_generated' => $cardsGenerated,
            'cards_used' => $cardsUsed,
            'cards_remaining' => max($cardsGenerated - $cardsUsed, 0),
            'created_at' => dateTimeFormat($this->created_at),
        ];
    }
}
