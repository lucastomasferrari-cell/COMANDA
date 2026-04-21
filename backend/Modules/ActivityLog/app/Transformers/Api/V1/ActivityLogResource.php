<?php

namespace Modules\ActivityLog\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ActivityLog\Models\ActivityLog;
use Modules\User\Transformers\Api\V1\UserSimpleResource;

/** @mixin ActivityLog */
class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $agent = $this->agent;

        return [
            'id' => $this->id,
            "ip" => $this->properties["info"]['ip'] ?? "-",
            'log_name' => $this->log_name,
            'description' => __($this->description, array_map(fn($param) => __($param), $this->properties["trans_params"] ?? [])),
            "subject" => $this->getSubjectText(),
            "agent" => [
                'desktop' => $agent['is_desktop'],
                'platform' => $agent['platform'] ?: null,
                'browser' => $agent['browser'] ?: null,
            ],
            'user' => new UserSimpleResource($this->causer),
            'event' => __("activitylog::activity_logs.events.$this->event"),
            "event_key" => $this->event,
            'created_at' => dateTimeFormat($this->created_at)
        ];
    }
}
