<?php

namespace Modules\Payment\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum PaymentMethod: string
{
    use EnumArrayable, EnumTranslatable;

    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case MobileWallet = 'mobile_wallet';
    case GiftCard = 'gift_card';


    /** @inheritDoc */
    public static function toArrayTrans(): array
    {
        $cases = [];

        foreach (self::values() as $value) {
            $cases[] = [
                "id" => $value,
                "name" => __(PaymentMethod::getTransKey() . ".$value"),
                "icon" => PaymentMethod::getIcon($value),
                "color" => PaymentMethod::getColor($value),
            ];
        }

        return $cases;
    }

    /** @inheritDoc */
    public static function getTransKey(): string
    {
        return "payment::enums.payment_methods";
    }

    /**
     * Get type icon by value
     *
     * @param string $value
     * @return string
     */
    public static function getIcon(string $value): string
    {
        return match ($value) {
            PaymentMethod::Cash->value => 'tabler-cash',
            PaymentMethod::Card->value => 'tabler-credit-card',
            PaymentMethod::BankTransfer->value => 'tabler-building-bank',
            PaymentMethod::MobileWallet->value => 'tabler-device-mobile-dollar',
            PaymentMethod::GiftCard->value => 'tabler-gift-card',

        };
    }

    /**
     * Get type color by value
     *
     * @param string $value
     * @return string
     */
    public static function getColor(string $value): string
    {
        return match ($value) {
            PaymentMethod::Card->value => '#27AE60',
            PaymentMethod::Cash->value => '#74B9FF',
            PaymentMethod::BankTransfer->value => '#0abde3',
            PaymentMethod::MobileWallet->value => '#01a3a4',
            PaymentMethod::GiftCard->value => '#f39c12',
        };
    }

    /**
     * Get enum as array value with trans
     *
     * @return array
     */
    public function toTrans(): array
    {
        return [
            "id" => $this->value,
            "name" => $this->trans(),
            "icon" => PaymentMethod::getIcon($this->value),
            "color" => PaymentMethod::getColor($this->value),
        ];
    }
}
