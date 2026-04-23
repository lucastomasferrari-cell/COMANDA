<?php

namespace Modules\Pos\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

class CloseSessionRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'declared_cash' => 'required|numeric|min:0|max:99999999999999',
            'justification' => 'nullable|string|max:2000',
            'manager_approval_token' => 'nullable|string',
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "pos::attributes.pos_sessions";
    }
}
