<?php

namespace Modules\Printer\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Printer\Enum\PrinterPaperSize;

class SavePrinterRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules([
                "name" => "required|string|max:255",
            ]),
            ...$this->getBranchRule(),
            "is_active" => "required|boolean",
            'options' => "required|array",
            'options.qintrix_id' => "required|string|max:255",
            'options.paper_size' => ['required', Rule::enum(PrinterPaperSize::class)],
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "printer::attributes.printers";
    }
}
