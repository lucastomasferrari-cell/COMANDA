<?php

namespace Modules\GiftCard\Services\GiftCardBatch;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\GiftCard\Models\GiftCardBatch;
use Throwable;

/**
 * Contract for managing gift card issuance batches.
 */
interface GiftCardBatchServiceInterface
{
    /**
     * Get the human-readable module label.
     */
    public function label(): string;

    /**
     * Get paginated gift card batches using filters and sorting options.
     *
     * @param array<string, mixed> $filters
     * @param array<int, string>|null $sorts
     */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator;

    /**
     * Show a single gift card batch by database identifier or UUID-compatible lookup key.
     */
    public function show(int|string $id): GiftCardBatch;

    /**
     * Store a new gift card batch.
     *
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function store(array $data): GiftCardBatch;

    /**
     * Delete one or more gift card batches.
     */
    public function destroy(int|array|string $ids): bool;

    /**
     * Get structure filters for batch listing screens.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStructureFilters(): array;

    /**
     * Get metadata required to build gift card batch forms.
     *
     * @return array<string, mixed>
     */
    public function getFormMeta(): array;
}
