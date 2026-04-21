<?php

namespace Modules\User\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Models\User;

/** @mixin User */
class AuthResource extends JsonResource
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
            "branch_id" => $this->branch_id,
            'role' => $this->getRolesWithPermissions()[0],
            "assigned_to_branch" => $this->assignedToBranch(),
        ];
    }
}
