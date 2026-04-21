<?php

namespace Modules\GiftCard\Services\GiftCardTransaction;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardTransaction;
use Throwable;

/**
 * Contract for querying and recording gift card ledger transactions.
 */
interface GiftCardTransactionServiceInterface
{
    /**
     * Get paginated gift card transactions using filters and sorting options.
     *
     * @param array<string, mixed> $filters
     * @param array<int, string>|null $sorts
     */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator;

    /**
     * Show a single gift card transaction by database identifier or UUID.
     */
    public function show(int|string $id): GiftCardTransaction;

    /**
     * Resolve a gift card transaction or fail by database identifier or UUID.
     */
    public function findOrFail(int|string $id): GiftCardTransaction;

    /**
     * Get structure filters for transaction listing screens.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStructureFilters(): array;

    /**
     * Record a balance-affecting transaction for the given gift card.
     *
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function record(GiftCard $giftCard, array $data): GiftCardTransaction;

    /**
     * Expire all eligible gift cards and return the affected count.
     */
    public function expireEligibleCards(): int;
}
