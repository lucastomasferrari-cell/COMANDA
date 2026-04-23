<?php

namespace Modules\Order\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

/**
 * Body esperado:
 *   { "custom_name": "Aperol spritz invitado", "custom_price": 5000.00,
 *     "custom_description": "cortesia de la casa", "quantity": 1 }
 *
 * El endpoint lo recibe en edit mode — la orden ya existe y vamos a
 * crear un order_product directamente, sin pasar por el cart facade.
 */
class StoreCustomOrderProductRequest extends Request
{
    public function rules(): array
    {
        return [
            "custom_name" => "required|string|min:1|max:255",
            "custom_price" => "required|numeric|min:0|max:9999999.99",
            "custom_description" => "nullable|string|max:2000",
            "quantity" => "required|integer|min:1|max:999",
            "manager_approval_token" => "nullable|string",
        ];
    }

    protected function availableAttributes(): string
    {
        return "order::attributes.orders";
    }
}
