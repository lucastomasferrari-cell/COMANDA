<?php

namespace Modules\GiftCard\Console;

use Illuminate\Console\Command;
use Modules\GiftCard\Services\GiftCardTransaction\GiftCardTransactionServiceInterface;

class ExpireGiftCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gift-cards:expire';

    /**
     * The console command description.
     */
    protected $description = 'Expire eligible gift cards and move their remaining balance into the transaction ledger.';

    /**
     * Execute the console command.
     */
    public function handle(GiftCardTransactionServiceInterface $service): int
    {
        $count = $service->expireEligibleCards();

        $this->info("Expired $count gift cards.");

        return self::SUCCESS;
    }
}
