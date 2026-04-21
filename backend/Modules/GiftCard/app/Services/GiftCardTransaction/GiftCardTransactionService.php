<?php

namespace Modules\GiftCard\Services\GiftCardTransaction;

use App\Forkiva;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Exceptions\InsufficientGiftCardBalanceException;
use Modules\GiftCard\Exceptions\InvalidGiftCardStateException;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\Support\GlobalStructureFilters;
use Modules\Support\Money;

class GiftCardTransactionService implements GiftCardTransactionServiceInterface
{
    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /**
     * Build the base transaction query with standard relations and card visibility rules.
     */
    protected function baseQuery()
    {
        return GiftCardTransaction::query()
            ->with([
                'giftCard:id,code,uuid',
                'branch:id,name',
                'createdBy:id,name',
                'order:id,reference_no,currency',
            ])
            ->whereHas('giftCard');
    }

    /** @inheritDoc */
    public function show(int|string $id): GiftCardTransaction
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int|string $id): GiftCardTransaction
    {
        return $this->baseQuery()
            ->where(fn($query) => $query->where('id', $id)->orWhere('uuid', $id))
            ->firstOrFail();
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();

        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            [
                'key' => 'currency',
                'label' => __('giftcard::attributes.gift_card_transactions.currency'),
                'type' => 'select',
                'options' => Currency::supportedList(),
            ],
            [
                'key' => 'order_currency',
                'label' => __('giftcard::attributes.gift_card_transactions.order_currency'),
                'type' => 'select',
                'options' => Currency::supportedList(),
            ],
            [
                'key' => 'type',
                'label' => __('giftcard::gift_card_transactions.filters.type'),
                'type' => 'select',
                'options' => GiftCardTransactionType::toArrayTrans(),
            ],
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function expireEligibleCards(): int
    {
        $count = 0;

        GiftCard::query()
            ->whereIn('status', [GiftCardStatus::Active->value, GiftCardStatus::Used->value])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Date::today())
            ->chunkById(100, function ($cards) use (&$count) {
                foreach ($cards as $card) {
                    if ($card->current_balance->greaterThan(new Money(0, $card->currency))) {
                        $this->record($card, [
                            'type' => GiftCardTransactionType::Expire,
                            'amount' => $card->current_balance->amount(),
                            'branch_id' => $card->branch_id,
                            'notes' => __('giftcard::messages.card_expired_automatically'),
                        ]);
                    } else {
                        $card->update(['status' => GiftCardStatus::Expired]);
                    }

                    $count++;
                }
            });

        return $count;
    }

    /** @inheritDoc */
    public function record(GiftCard $giftCard, array $data): GiftCardTransaction
    {
        $type = $data['type'] instanceof GiftCardTransactionType
            ? $data['type']
            : GiftCardTransactionType::from($data['type']);

        return DB::transaction(function () use ($giftCard, $data, $type) {
            /** @var GiftCard $lockedGiftCard */
            $lockedGiftCard = GiftCard::query()
                ->whereKey($giftCard->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedGiftCard->isExpired() && $type !== GiftCardTransactionType::Expire) {
                throw new InvalidGiftCardStateException(__('giftcard::messages.card_expired'));
            }

            if ($lockedGiftCard->isDisabled() && !in_array($type, [GiftCardTransactionType::Adjustment, GiftCardTransactionType::Expire], true)) {
                throw new InvalidGiftCardStateException(__('giftcard::messages.card_disabled'));
            }

            $amount = new Money($data['amount'], $lockedGiftCard->currency);
            $balanceBefore = $lockedGiftCard->current_balance;
            $direction = $type->affectsBalanceBy();
            $balanceAfter = $direction === -1
                ? $balanceBefore->subtract($amount)
                : $balanceBefore->add($amount);

            if ($balanceAfter->lessThan(new Money(0, $lockedGiftCard->currency))) {
                throw new InsufficientGiftCardBalanceException(__('giftcard::messages.insufficient_balance'));
            }

            $lockedGiftCard->current_balance = $balanceAfter->amount();
            $lockedGiftCard->status = $this->resolveStatus($lockedGiftCard, $type, $balanceAfter);
            $lockedGiftCard->save();

            return $lockedGiftCard->transactions()->create([
                'type' => $type,
                'amount' => $amount->amount(),
                'balance_before' => $balanceBefore->amount(),
                'balance_after' => $balanceAfter->amount(),
                'currency' => $lockedGiftCard->currency,
                'exchange_rate' => $data['exchange_rate'] ?? null,
                'amount_in_order_currency' => $data['amount_in_order_currency'] ?? null,
                'order_currency' => $data['order_currency'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? $lockedGiftCard->branch_id,
                'notes' => $data['notes'] ?? null,
                'transaction_at' => $data['transaction_at'] ?? now(),
            ]);
        });
    }

    /**
     * Resolve the resulting gift card status after recording a transaction.
     *
     * @param GiftCard $giftCard
     * @param GiftCardTransactionType $type
     * @param Money $balanceAfter
     * @return GiftCardStatus
     */
    protected function resolveStatus(GiftCard $giftCard, GiftCardTransactionType $type, Money $balanceAfter): GiftCardStatus
    {
        if ($type === GiftCardTransactionType::Expire || $giftCard->isExpired()) {
            return GiftCardStatus::Expired;
        }

        if ($giftCard->status === GiftCardStatus::Disabled) {
            return GiftCardStatus::Disabled;
        }

        return $balanceAfter->isZero() ? GiftCardStatus::Used : GiftCardStatus::Active;
    }
}
