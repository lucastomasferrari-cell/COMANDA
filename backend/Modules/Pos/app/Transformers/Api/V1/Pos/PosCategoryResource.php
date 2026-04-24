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
            // Opción A (post Sprint 2): solo color_hue — el frontend deriva
            // HSL(hue 55% 50%) para chips activos, HSL(hue 40% 85%) para el
            // placeholder bg en light, etc. Antes exponíamos también "color"
            // hex pero el select del PosViewerService no lo incluía (solo
            // cargaba el modelo parcial) y Laravel 12 tiraba "attribute does
            // not exist" al hacer $this->color.
            "color_hue" => $this->color_hue,
            'items' => PosCategoryResource::collection($this->whenLoaded('childrenRecursive')),
        ];
    }
}
