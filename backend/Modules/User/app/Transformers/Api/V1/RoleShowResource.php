<?php

namespace Modules\User\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Models\Role;

/** @mixin Role */
class RoleShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new RoleResource($this))->toArray($request),
            "permissions" => $this->getPermissionNames()->toArray(),
        ];
    }
}
