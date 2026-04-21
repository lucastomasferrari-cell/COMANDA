<?php

namespace Modules\GiftCard\Services\GiftCardBatch;

use App\Forkiva;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Branch\Models\Branch;
use Modules\GiftCard\Enums\GiftCardScope;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardBatch;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeServiceInterface;
use Modules\Support\GlobalStructureFilters;

class GiftCardBatchService implements GiftCardBatchServiceInterface
{
    public function __construct(protected GiftCardCodeServiceInterface $codeService)
    {
    }

    /** @inheritDoc */
    public function label(): string
    {
        return __('giftcard::gift_card_batches.gift_card_batch');
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return GiftCardBatch::query()
            ->with('branch:id,name')
            ->withCount([
                'cards as cards_generated',
                'cards as cards_used' => fn($query) => $query->whereIn('status', [
                    GiftCardStatus::Used->value,
                    GiftCardStatus::Expired->value,
                ]),
            ])
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function show(int|string $id): GiftCardBatch
    {
        return GiftCardBatch::query()
            ->with('branch:id,name')
            ->withCount([
                'cards as cards_generated',
                'cards as cards_used' => fn($query) => $query->whereIn('status', [
                    GiftCardStatus::Used->value,
                    GiftCardStatus::Expired->value,
                ]),
            ])
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function store(array $data): GiftCardBatch
    {
        $data = $this->resolveDerivedAttributes($data);

        return DB::transaction(function () use ($data) {
            /** @var GiftCardBatch $batch */
            $batch = GiftCardBatch::query()->create($data);

            $scope = $batch->branch_id ? GiftCardScope::Branch : GiftCardScope::Global;

            for ($index = 0; $index < $batch->quantity; $index++) {
                GiftCard::query()
                    ->create([
                        'gift_card_batch_id' => $batch->id,
                        'branch_id' => $batch->branch_id,
                        'scope' => $scope,
                        'status' => GiftCardStatus::Active,
                        'code' => $this->codeService->generate($batch->prefix),
                        'initial_balance' => $batch->value->amount(),
                        'current_balance' => $batch->value->amount(),
                        'currency' => $batch->currency,
                    ]);
            }

            return $batch->load('branch:id,name')
                ->loadCount([
                    'cards as cards_generated',
                    'cards as cards_used' => fn($query) => $query->whereIn('status', [
                        GiftCardStatus::Used->value,
                        GiftCardStatus::Expired->value,
                    ]),
                ]);
        });
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return GiftCardBatch::query()
            ->whereIn('id', parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();

        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(): array
    {
        return [
            'branches' => Branch::list(),
            'default_currency' => setting('default_currency'),
        ];
    }

    /**
     * Resolve batch currency from the selected branch or the default currency.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function resolveDerivedAttributes(array $data): array
    {
        $branchId = blank($data['branch_id'] ?? null) ? null : (int) $data['branch_id'];
        $branch = $branchId ? Branch::query()->find($branchId) : null;

        return [
            ...$data,
            'branch_id' => $branch?->id,
            'currency' => $branch?->currency ?? setting('default_currency'),
        ];
    }
}
