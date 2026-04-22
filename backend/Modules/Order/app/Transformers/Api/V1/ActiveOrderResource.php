<?php

namespace Modules\Order\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\Enums\OrderType;
use Modules\Order\Models\Order;
use Modules\Support\Enums\DateTimeFormat;

/** @mixin Order */
class ActiveOrderResource extends JsonResource
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
            "customer_name" => $this->getCustomerName(),
            "type" => $this->type->toTrans(),
            "previous_status" => $this->previous_status?->toTrans(),
            "status" => $this->status->toTrans(),
            "next_status" => $this->next_status?->toTrans(),
            "payment_status" => $this->payment_status->toTrans(),
            "total" => $this->total,
            "due_amount" => $this->due_amount,
            "allow_refund" => $this->refundIsAllowed(),
            "allow_cancel" => $this->cancelIsAllowed(),
            "allow_update_status" => $this->allowUpdateStatus(),
            "allow_edit" => $this->editIsAllowed(),
            "is_scheduled_order" => !is_null($this->scheduled_at),
            "is_scheduled_for_today" => $this->isScheduledForToday(),
            "scheduled_at" => dateTimeFormat($this->scheduled_at),
            "table" => $this->type === OrderType::DineIn
                ? [
                    "id" => $this->table_id,
                    "name" => $this->relationLoaded("table") ? $this->table?->name : null,
                ]
                : null,
            "products" => [
                "count" => $this->products->count(),
                "names" => implode(",", $this->products->pluck('name')->toArray()),
            ],
            "time" => dateTimeFormat($this->created_at, DateTimeFormat::Time),
            "date" => dateTimeFormat($this->created_at, DateTimeFormat::Date),
            "bill_requested_at" => $this->bill_requested_at?->toIso8601String(),
            "paused_at" => $this->paused_at?->toIso8601String(),
        ];
    }
}
