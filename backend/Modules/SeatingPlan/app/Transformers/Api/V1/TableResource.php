<?php

namespace Modules\SeatingPlan\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\SeatingPlan\Models\Table;

/** @mixin Table */
class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "qrcode" => $this->uuid,
            "name" => $this->name,
            "branch" => [
                "id" => $this->branch_id,
                "name" => $this->relationLoaded("branch") ? $this->branch?->name : "",
            ],
            "floor" => [
                "id" => $this->floor_id,
                "name" => $this->relationLoaded("floor") ? $this->floor?->name : "",
            ],
            "zone" => [
                "id" => $this->zone_id,
                "name" => $this->relationLoaded("zone") ? $this->zone?->name : "",
            ],
            "capacity" => $this->capacity,
            "status" => $this->status?->toTrans(),
            "shape" => $this->shape->toTrans(),
            "position_x" => $this->position_x,
            "position_y" => $this->position_y,
            "width" => (float) $this->width,
            "height" => (float) $this->height,
            "rotation" => (int) $this->rotation,
            "is_active" => $this->is_active,
            "updated_at" => dateTimeFormat($this->updated_at),
            "created_at" => dateTimeFormat($this->created_at),
        ];
    }
}
