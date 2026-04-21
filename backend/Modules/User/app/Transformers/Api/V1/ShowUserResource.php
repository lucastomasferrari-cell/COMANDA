<?php

namespace Modules\User\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Models\User;

/** @mixin User */
class ShowUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new UserResource($this))->resolve($request),
            "category_slugs" => $this->category_slugs,
            "printer" => [
                "id" => $this->printer_id,
                "name" => $this->relationLoaded("printer") ? $this->printer?->name : null,
            ]
        ];
    }
}
