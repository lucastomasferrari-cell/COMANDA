<?php

namespace Modules\GiftCard\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

class StoreGiftCardBatchRequest extends Request
{
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules(['name' => 'required|string|max:255']),
            'prefix' => ['nullable', 'string', 'max:25'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'value' => ['required', 'numeric', 'min:0.0001', 'max:99999999999999'],
            ...$this->getBranchRule(true),
        ];
    }

    /**
     * Get the translation namespace used for request attribute labels.
     */
    protected function availableAttributes(): string
    {
        return 'giftcard::attributes.gift_card_batches';
    }
}
