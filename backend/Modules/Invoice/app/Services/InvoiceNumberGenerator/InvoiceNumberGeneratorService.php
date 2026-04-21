<?php

namespace Modules\Invoice\Services\InvoiceNumberGenerator;

use Illuminate\Support\Facades\DB;
use Modules\Branch\Models\Branch;
use Modules\Invoice\Models\InvoiceCounter;

class InvoiceNumberGeneratorService implements InvoiceNumberGeneratorServiceInterface
{
    /** @inheritDoc */
    public function generate(Branch $branch, string $prefix = 'INV'): array
    {
        return DB::transaction(function () use ($branch, $prefix) {

            $counter = InvoiceCounter::query()
                ->where('cr_number', $branch->registration_number)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['cr_number' => $branch->registration_number],
                    ['last_counter' => 0]
                );

            $counter->increment('last_counter');

            return [
                'counter' => $counter->last_counter,
                'number' => sprintf('%s-%02d-%05d', strtoupper($prefix), $branch->id, $counter->last_counter),
            ];
        });
    }
}
