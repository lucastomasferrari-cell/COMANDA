<?php

namespace Modules\AuditLog\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AuditLog\Models\AuditLog;

/** @mixin AuditLog */
class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "user" => [
                "id" => $this->user_id,
                "name" => $this->relationLoaded("user") ? $this->user?->name : null,
            ],
            "approver" => $this->approved_by ? [
                "id" => $this->approved_by,
                "name" => $this->relationLoaded("approver") ? $this->approver?->name : null,
            ] : null,
            "auditable" => [
                "type" => $this->auditable_type,
                "id" => $this->auditable_id,
            ],
            "action" => $this->action,
            "reason" => $this->reason,
            "old_values" => $this->old_values,
            "new_values" => $this->new_values,
            "metadata" => $this->metadata,
            "parent_id" => $this->parent_id,
            "is_fiscal" => $this->is_fiscal,
            "created_at" => dateTimeFormat($this->created_at),
        ];
    }
}
