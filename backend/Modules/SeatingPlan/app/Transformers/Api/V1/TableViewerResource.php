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
        ];
    }
}
