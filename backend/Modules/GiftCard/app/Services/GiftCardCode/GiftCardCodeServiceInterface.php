<?php

namespace Modules\GiftCard\Services\GiftCardCode;

interface GiftCardCodeServiceInterface
{
    /**
     * Generate a unique gift card code.
     *
     * @param string|null $prefix
     * @return string
     */
    public function generate(?string $prefix = null): string;

    /**
     * Normalize a user-entered gift card code for resilient lookup.
     *
     * @param string $code
     * @return string
     */
    public function normalize(string $code): string;
}
