<?php

namespace Modules\GiftCard\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\GiftCard\Enums\GiftCardStatus;

class SaveGiftCardRequest extends Request
{
    public function rules(): array
    {
        $giftCardId = $this->route('id');

        return [
            'code' => ['nullable', 'string', 'max:255', Rule::unique('gift_cards', 'code')->ignore($giftCardId)],
            'initial_balance' => ['required', 'numeric', 'min:0.0001', 'max:99999999999999'],
            ...$this->getBranchRule(true),
            'customer_id' => ['nullable', 'numeric', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'status' => ['required', Rule::enum(GiftCardStatus::class)],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    /**
     * Get the translation namespace used for request attribute labels.
     */
    protected function availableAttributes(): string
    {
        return 'giftcard::attributes.gift_cards';
    }
}
