<?php

namespace Modules\SeatingPlan\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

class UpdateTablePositionsRequest extends Request
{
    /**
     * Payload: { "positions": [{ "id": 1, "position_x": 100, "position_y": 80,
     *           "width": 80, "height": 80, "rotation": 0 }, ...] }
     *
     * Solo update de layout, no toca status/capacity/name/etc.
     */
    public function rules(): array
    {
        return [
            "positions" => "required|array|min:1",
            "positions.*.id" => "required|integer|exists:tables,id,deleted_at,NULL",
            "positions.*.position_x" => "required|numeric|between:-99999.99,99999.99",
            "positions.*.position_y" => "required|numeric|between:-99999.99,99999.99",
            "positions.*.width" => "required|numeric|min:10|max:9999.99",
            "positions.*.height" => "required|numeric|min:10|max:9999.99",
            "positions.*.rotation" => "nullable|integer|min:0|max:359",
        ];
    }

    protected function availableAttributes(): string
    {
        return "seatingplan::attributes.tables";
    }
}
