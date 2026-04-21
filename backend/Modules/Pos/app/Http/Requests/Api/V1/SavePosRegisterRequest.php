<?php

namespace Modules\Pos\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

class SavePosRegisterRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules([
                "name" => "required|string|max:255",
                "note" => "nullable|string|max:255",
            ]),
            ...$this->getBranchRule(),
            "code" => "bail|required|max:50|unique:pos_registers,code,{$this->route('id')}",
            "bill_printer_id" => "bail|nullable|exists:printers,id,is_active,1",
            "invoice_printer_id" => "bail|nullable|exists:printers,id,is_active,1",
            "waiter_printer_id" => "bail|nullable|exists:printers,id,is_active,1",
            // TODO: Remove comment on support delivery
//            "delivery_printer_id" => "bail|nullable|exists:printers,id,is_active,1",
            "is_active" => "required|boolean",
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "pos::attributes.pos_registers";
    }
}
