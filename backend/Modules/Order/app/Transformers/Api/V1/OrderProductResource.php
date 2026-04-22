<?php

namespace Modules\Order\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Media\Transformers\Api\V1\MediaSimpleResource;
use Modules\Order\Models\OrderProduct;

/** @mixin OrderProduct */
class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $isCustom = is_null($this->product_id);

        return [
            "id" => $this->id,
            "is_custom" => $isCustom,
            "product" => [
                "id" => $this->product_id,
                ...($isCustom
                    ? [
                        "name" => $this->custom_name,
                        "description" => $this->custom_description,
                        "thumbnail" => null,
                    ]
                    : ($this->relationLoaded("product")
                        ? [
                            "name" => $this->product->name,
                            "thumbnail" => $this->product->thumbnail != null
                                ? new MediaSimpleResource($this->product->thumbnail)
                                : null,
                        ]
                        : []))
            ],
            "status" => $this->status->toTrans(),
            "currency" => $this->currency,
            "currency_rate" => $this->currency_rate,
            "unit_price" => $this->unit_price->withConvertedDefaultCurrency($this->currency_rate),
            "quantity" => $this->quantity,
            "subtotal" => $this->subtotal->withConvertedDefaultCurrency($this->currency_rate),
            "tax_total" => $this->tax_total->withConvertedDefaultCurrency($this->currency_rate),
            "total" => $this->total->withConvertedDefaultCurrency($this->currency_rate),
            ...(auth()->user()->can('admin.orders.financials')
                ? [
                    "cost_price" => $this->cost_price->withConvertedDefaultCurrency($this->currency_rate),
                    "revenue" => $this->revenue->withConvertedDefaultCurrency($this->currency_rate),
                ] : []),
            "options" => OrderProductOptionResource::collection($this->whenLoaded('options')),
            "taxes" => OrderTaxResource::collection($this->whenLoaded('taxes')),
            "created_at" => dateTimeFormat($this->created_at),
        ];
    }
}
