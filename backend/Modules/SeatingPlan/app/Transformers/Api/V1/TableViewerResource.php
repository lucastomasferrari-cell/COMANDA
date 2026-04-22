<?php

namespace Modules\SeatingPlan\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\SeatingPlan\Models\Table;

/** @mixin Table */
class TableViewerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "floor" => [
                "id" => $this->floor_id,
                "name" => $this->relationLoaded("floor") ? $this->floor?->name : "",
            ],
            "zone" => [
                "id" => $this->zone_id,
                "name" => $this->relationLoaded("zone") ? $this->zone?->name : "",
            ],
            "has_merged" => !is_null($this->current_merge_id),
            "status" => $this->status->toTrans(),
            "shape" => $this->shape?->value,
            "position_x" => $this->position_x,
            "position_y" => $this->position_y,
            "width" => (float) $this->width,
            "height" => (float) $this->height,
            "rotation" => (int) $this->rotation,
            "capacity" => $this->capacity,
            "active_order" => $this->whenLoaded("activeOrder", fn () => $this->activeOrder ? [
                "id" => $this->activeOrder->id,
                "guest_count" => $this->activeOrder->guest_count,
                "total" => $this->activeOrder->total,
                "created_at" => $this->activeOrder->created_at?->toIso8601String(),
            ] : null),
        ];
    }
}
