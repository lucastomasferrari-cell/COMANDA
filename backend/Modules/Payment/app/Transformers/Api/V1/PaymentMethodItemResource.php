<?php

namespace Modules\Payment\Transformers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payment\Models\PaymentMethodItem;

/** @mixin PaymentMethodItem */
class PaymentMethodItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->toTrans(),
            'impacts_cash' => (bool) $this->impacts_cash,
            'is_active' => (bool) $this->is_active,
            'order' => $this->order,
            'updated_at' => dateTimeFormat($this->updated_at),
            'created_at' => dateTimeFormat($this->created_at),
        ];
    }
}
