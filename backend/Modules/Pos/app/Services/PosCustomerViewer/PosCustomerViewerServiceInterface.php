<?php

namespace Modules\Pos\Services\PosCustomerViewer;

interface PosCustomerViewerServiceInterface
{
    /**
     * Build customer view snapshot from cart.
     *
     * @return array
     */
    public function snapshot(): array;

    /**
     * Get cart fingerprint to detect changes.
     *
     * @return string
     */
    public function fingerprint(): string;
}
