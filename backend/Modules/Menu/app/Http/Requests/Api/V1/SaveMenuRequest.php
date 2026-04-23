<?php

namespace Modules\Menu\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveMenuRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules([
                "name" => "required|string|max:255",
                "description" => "nullable|string|max:500",
            ]),
            ...$this->getBranchRule(),
            "sku" => [
                'bail',
                'nullable',
                'string',
                'max:255',
                Rule::unique('menus', 'sku')->ignore($this->route('id')),
            ],
            "is_active" => "required|boolean",
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "menu::attributes.menus";
    }
}
