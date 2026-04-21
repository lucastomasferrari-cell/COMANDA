<?php

namespace Modules\SeatingPlan\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\Transformers\Api\V1\ActiveOrderResource;
use Modules\SeatingPlan\Models\Table;

/** @mixin Table */
class TableShowViewerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $currentMerge = !is_null($this->current_merge_id)
        && $this->relationLoaded("currentMerge")
        && !is_null($this->currentMerge)
            ? [
                "id" => $this->current_merge_id,
                "type" => $this->currentMerge->type->toTrans(),
                "primary_table_id" => $this->currentMerge->table_id,
                "is_primary" => $this->currentMerge->table_id == $this->id,
                "members" => $this->currentMerge->relationLoaded("members")
                    ? $this->currentMerge
                        ->members
                        ->map(fn($member) => [
                            "id" => $member->id,
                            "table" => [
                                "id" => $member->table_id,
                                "name" => $member->relationLoaded("table") ? $member->table->name : "",
                            ],
                            "is_main" => $member->is_main,
                        ])
                    : [],
            ] : null;

        $order = $this->relationLoaded('viewerOrder') ? $this->getRelation('viewerOrder') : null;
        $orders = $this->relationLoaded('viewerOrders') ? $this->getRelation('viewerOrders') : null;

        $data = [
            ...((new TableViewerResource($this))->resolve()),
            "uuid" => $this->uuid,
            "capacity" => $this->capacity,
            "waiter" => [
                "id" => $this->assigned_waiter_id,
                "name" => $this->relationLoaded("waiter") ? $this->waiter?->name : "",
            ],
            "current_merge" => $currentMerge,
            "allow_split" => !is_null($currentMerge) && is_null($order) && (is_null($orders) || $orders->count() === 0),
            "shape" => $this->shape->toTrans(),
        ];

        if (!is_null($order)) {
            $data['order'] = new ActiveOrderResource($order);
        } else if (!is_null($orders) && $orders->count() > 0) {
            $data['orders'] = ActiveOrderResource::collection($orders);
        }

        return $data;
    }
}
