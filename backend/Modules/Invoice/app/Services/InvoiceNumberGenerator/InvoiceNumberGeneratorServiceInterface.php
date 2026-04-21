<?php

namespace Modules\Invoice\Services\InvoiceNumberGenerator;

use Modules\Branch\Models\Branch;
use Throwable;

interface InvoiceNumberGeneratorServiceInterface
{
    /**
     * Generate invoice number
     *
     * @param Branch $branch
     * @param string $prefix
     * @return array
     * @throws Throwable
     */
    public function generate(Branch $branch, string $prefix = "INV"): array;
}
