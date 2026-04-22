<?php

namespace Modules\Order\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

/**
 * Body esperado:
 *   { "void_reason_id": 3, "void_note": "El cliente no lo pidió" }
 *
 * void_reason_id: obligatorio cuando el item ya fue enviado a cocina
 * (status != 'pending'). Para items pending, el cajero puede anular
 * con reason_id opcional (UI precarga un dropdown de razones comunes).
 *
 * void_note: obligatorio cuando la razón tiene código "item_other".
 * Se enforcea en el service leyendo la reason, no en la request.
 */
class VoidOrderProductRequest extends Request
{
    public function rules(): array
    {
        return [
            "void_reason_id" => "nullable|integer|exists:void_reasons,id",
            "void_note" => "nullable|string|max:2000",
            "manager_approval_token" => "nullable|string",
        ];
    }

    protected function availableAttributes(): string
    {
        return "order::attributes.orders";
    }
}
