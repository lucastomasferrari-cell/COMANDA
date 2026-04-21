<?php

namespace Modules\GiftCard\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Branch\Models\Branch;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\GiftCard\Models\GiftCardBatch;

class GiftCardTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $primaryBranch = Branch::query()->withoutGlobalActive()->oldest('id')->first();
        $batch = GiftCardBatch::query()
            ->when($primaryBranch, fn($query) => $query->where('branch_id', $primaryBranch->id))
            ->inRandomOrder()
            ->first()
            ?? GiftCardBatch::factory()->withBranch($primaryBranch?->id)->create();

        $saleOnlyCard = GiftCard::factory()
            ->linkedToBatch($batch)
            ->branchScoped($primaryBranch?->id)
            ->active()
            ->withBalance(100, 100)
            ->state(['notes' => 'Case: purchase only'])
            ->create();
        $this->createTransaction($saleOnlyCard, 'purchase', 100, 0, 100);

        $partiallyRedeemedCard = GiftCard::factory()
            ->linkedToBatch($batch)
            ->branchScoped($primaryBranch?->id)
            ->active()
            ->withBalance(150, 110)
            ->state(['notes' => 'Case: partially redeemed'])
            ->create();
        $this->createTransaction($partiallyRedeemedCard, 'purchase', 150, 0, 150);
        $this->createTransaction($partiallyRedeemedCard, 'redeem', 40, 150, 110, true);

        $usedCard = GiftCard::factory()
            ->branchScoped($primaryBranch?->id)
            ->used()
            ->withBalance(75, 0)
            ->state(['notes' => 'Case: fully used'])
            ->create();
        $this->createTransaction($usedCard, 'purchase', 75, 0, 75);
        $this->createTransaction($usedCard, 'redeem', 75, 75, 0, true);

        $rechargedCard = GiftCard::factory()
            ->branchScoped($primaryBranch?->id)
            ->active()
            ->withBalance(50, 60)
            ->state(['notes' => 'Case: recharged after redemption'])
            ->create();
        $this->createTransaction($rechargedCard, 'purchase', 50, 0, 50);
        $this->createTransaction($rechargedCard, 'redeem', 20, 50, 30, true);
        $this->createTransaction($rechargedCard, 'recharge', 30, 30, 60);

        $refundCard = GiftCard::factory()
            ->branchScoped($primaryBranch?->id)
            ->active()
            ->withBalance(20, 45)
            ->state(['notes' => 'Case: refunded to gift card'])
            ->create();
        $this->createTransaction($refundCard, 'purchase', 20, 0, 20);
        $this->createTransaction($refundCard, 'refund', 25, 20, 45);

        $crossCurrencyRedeemCard = GiftCard::factory()
            ->global()
            ->active()
            ->withBalance(100, 70)
            ->state(['notes' => 'Case: redeemed with order currency conversion'])
            ->create();
        $this->createTransaction($crossCurrencyRedeemCard, 'purchase', 100, 0, 100);
        $this->createTransaction($crossCurrencyRedeemCard, 'redeem', 30, 100, 70, true);

        $adjustedCard = GiftCard::factory()
            ->branchScoped($primaryBranch?->id)
            ->active()
            ->withBalance(100, 130)
            ->state(['notes' => 'Case: manual adjustment'])
            ->create();
        $this->createTransaction($adjustedCard, 'purchase', 100, 0, 100);
        $this->createTransaction($adjustedCard, 'adjustment', 30, 100, 130);

        $expiredCard = GiftCard::factory()
            ->branchScoped($primaryBranch?->id)
            ->expired()
            ->withBalance(90, 0)
            ->state([
                'status' => GiftCardStatus::Expired->value,
                'notes' => 'Case: expired remaining balance',
            ])
            ->create();
        $this->createTransaction($expiredCard, 'purchase', 90, 0, 90);
        $this->createTransaction($expiredCard, 'redeem', 15, 90, 75, true);
        $this->createTransaction($expiredCard, 'expire', 75, 75, 0);

        GiftCardTransaction::factory()->count(8)->create();
    }

    /**
     * Create a single transaction row for the given gift card case.
     */
    protected function createTransaction(
        GiftCard $giftCard,
        string $type,
        float|int $amount,
        float|int $balanceBefore,
        float|int $balanceAfter,
        bool $withOrderCurrency = false,
    ): GiftCardTransaction {
        $factory = GiftCardTransaction::factory()
            ->forGiftCard($giftCard)
            ->state([
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'currency' => $giftCard->currency,
            ]);

        if ($withOrderCurrency) {
            $factory = $factory->withOrderCurrency();
        }

        return $factory->create();
    }
}
