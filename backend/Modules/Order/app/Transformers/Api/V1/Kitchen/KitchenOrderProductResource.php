<?php

namespace Modules\Order\Transformers\Api\V1\Kitchen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\Models\OrderProduct;

/** @mixin OrderProduct */
class KitchenOrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "product" => [
                "id" => $this->product_id,
                ...($this->relationLoaded("product")
                    ? [
                        "name" => $this->product->name,
                    ]
                    : [])
            ],
            "status" => $this->status->toTrans(),
            "next_status" => $this->status->nextStatus()?->toTrans(),
            "quantity" => $this->quantity,
            "options" => KitchenOrderProductOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
