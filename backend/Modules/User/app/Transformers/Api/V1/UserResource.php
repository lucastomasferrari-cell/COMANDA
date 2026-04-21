<?php

namespace Modules\User\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Models\User;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            "branch" => [
                "id" => $this->branch_id,
                "name" => $this->relationLoaded("branch") ? $this->branch?->name : "",
            ],
            "profile_photo_url" => $this->profile_photo_url,
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'phone_country_iso_code' => $this->phone_country_iso_code,
            "national_phone" => $this->national_phone,
            'email' => $this->email,
            'gender' => $this->gender?->toTrans(),
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'note' => $this->note,
            'customer_type' => $this->customer_type?->toTrans(),
            'registration_number' => $this->registration_number,
            'vat_tin' => $this->vat_tin,
            'is_active' => $this->is_active,
            "role" => isset($this->roles[0])
                ? [
                    "id" => $this->roles[0]->id,
                    "name" => $this->roles[0]->name,
                    "display_name" => $this->roles[0]->display_name
                ] : null,
            "is_main_user" => $this->isMainUser(),
            "updated_at" => dateTimeFormat($this->updated_at),
            "created_at" => dateTimeFormat($this->created_at),
        ];
    }
}
