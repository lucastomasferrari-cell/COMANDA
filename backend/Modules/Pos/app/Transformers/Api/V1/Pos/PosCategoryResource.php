<?php

namespace Modules\Pos\Transformers\Api\V1\Pos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Category\Models\Category;

/** @mixin Category */
class PosCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "logo" => $this->logo?->preview_image_url,
            // Color y color_hue: el frontend del POS los usa para pintar el
            // chip activo (color hex existente) y el placeholder de
            // productos sin foto (hue del Sprint 1.B).
            "color" => $this->color,
            "color_hue" => $this->color_hue,
            'items' => PosCategoryResource::collection($this->whenLoaded('childrenRecursive')),
        ];
    }
}
