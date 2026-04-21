<?php

namespace Modules\GiftCard\Services\GiftCard;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\GiftCard\Models\GiftCard;
use Modules\Support\Money;

/**
 * Contract for gift card catalog, balance, and operational actions.
 */
interface GiftCardServiceInterface
{
    /**
     * Get the human-readable module label.
     */
    public function label(): string;

    /**
     * Get the fully qualified gift card model class name.
     *
     * @return class-string<GiftCard>
     */
    public function model(): string;

    /**
     * Create a new gift card model instance.
     */
    public function getModel(): GiftCard;

    /**
     * Get paginated gift cards using filters and sorting options.
     *
     * @param array<string, mixed> $filters
     * @param array<int, string>|null $sorts
     */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator;

    /**
     * Show a single gift card by database identifier, UUID, or supported lookup key.
     */
    public function show(int|string $id): GiftCard;

    /**
     * Resolve a gift card or fail by database identifier, UUID, or code.
     */
    public function findOrFail(int|string $id): Builder|array|EloquentCollection|GiftCard;

    /**
     * Store a new gift card record.
     *
     * @param array<string, mixed> $data
     */
    public function store(array $data): GiftCard;

    /**
     * Update an existing gift card record.
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data): GiftCard;

    /**
     * Delete one or more gift cards.
     */
    public function destroy(int|array|string $ids): bool;

    /**
     * Get structure filters for gift card listing screens.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStructureFilters(): array;

    /**
     * Get metadata required to build gift card forms.
     *
     * @return array<string, mixed>
     */
    public function getFormMeta(): array;

    /**
     * Get the current balance payload for a gift card.
     *
     * @return array<string, mixed>
     */
    public function balance(int|string $id): array;

    /**
     * Convert an order-currency amount into the target gift card currency.
     *
     * @param GiftCard $giftCard
     * @param float $amount
     * @param string $orderCurrency
     * @param float|int|null $orderCurrencyRate
     * @return Money
     */
    public function convertOrderAmountToGiftCardAmount(
        GiftCard $giftCard,
        float $amount,
        string $orderCurrency,
        float|int|null $orderCurrencyRate,
    ): Money;
}
