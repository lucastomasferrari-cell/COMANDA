<?php

namespace Modules\User\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Models\User;

/** @mixin User */
class MeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "profile_photo_url" => $this->profile_photo_url,
            "username" => $this->username,
            "email" => $this->email,
            "gender" => $this->gender->toTrans(),
            "branch" => is_null($this->branch_id)
                ? null
                : [
                    "id" => $this->branch_id,
                    "name" => $this->relationLoaded("branch") ? $this->branch?->name : "",
                ],
            'role' => $this->getRolesWithPermissions()[0],
        ];
    }
}
