<?php

namespace Modules\Order\Transformers\Api\V1\Kitchen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\Enums\OrderType;
use Modules\Order\Models\Order;
use Modules\Support\Enums\DateTimeFormat;

/** @mixin Order */
class KitchenOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "reference_no" => $this->reference_no,
            "order_number" => $this->order_number,
            "status" => $this->status->toTrans(),
            "next_status" => $this->next_status->toTrans(),
            "type" => $this->type->toTrans(),
            "payment_status" => $this->payment_status->toTrans(),
            "table" => $this->type === OrderType::DineIn
                ? [
                    "id" => $this->table_id,
                    "name" => $this->relationLoaded("table") ? $this->table?->name : null,
                ]
                : null,
            "products" => KitchenOrderProductResource::collection($this->whenLoaded("products")),
            "time" => dateTimeFormat($this->created_at, DateTimeFormat::Time),
            "date" => dateTimeFormat($this->created_at, DateTimeFormat::Date),
        ];
    }
}
