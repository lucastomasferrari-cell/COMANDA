<?php

namespace Modules\GiftCard\Services\GiftCard;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use InvalidArgumentException;
use Modules\Branch\Models\Branch;
use Modules\Currency\Currency;
use Modules\Currency\Models\CurrencyRate;
use Modules\GiftCard\Enums\GiftCardScope;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeServiceInterface;
use Modules\Support\GlobalStructureFilters;
use Modules\Support\Money;
use Modules\User\Enums\DefaultRole;
use Modules\User\Models\User;

class GiftCardService implements GiftCardServiceInterface
{
    public function __construct(protected GiftCardCodeServiceInterface $codeService)
    {
    }

    /** @inheritDoc */
    public function label(): string
    {
        return __('giftcard::gift_cards.gift_card');
    }

    /** @inheritDoc */
    public function show(int|string $id): GiftCard
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int|string $id): Builder|array|EloquentCollection|GiftCard
    {
        $normalizedCode = is_string($id) ? $this->codeService->normalize($id) : null;

        return $this->baseQuery()
            ->where(function (Builder $query) use ($id, $normalizedCode) {
                $query->where('id', $id)
                    ->orWhere('uuid', $id);

                if (!blank($normalizedCode)) {
                    $query->orWhereRaw(
                        "REPLACE(REPLACE(UPPER(`code`), '-', ''), ' ', '') = ?",
                        [$normalizedCode]
                    );
                }
            })
            ->firstOrFail();
    }

    /**
     * Build the base gift card query with standard relations and visibility rules.
     */
    protected function baseQuery(): Builder
    {
        return $this->getModel()
            ->query()
            ->with([
                'branch:id,name',
                'customer:id,name',
                'batch:id,name',
            ]);
    }

    /** @inheritDoc */
    public function getModel(): GiftCard
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return GiftCard::class;
    }

    /** @inheritDoc */
    public function store(array $data): GiftCard
    {
        $data = $this->resolveDerivedAttributes($data);

        $giftCard = $this->getModel()
            ->query()
            ->create([
                ...$data,
                'current_balance' => $data['initial_balance'],
            ]);

        return $giftCard->loadMissing(['branch:id,name', 'customer:id,name', 'batch:id,name']);
    }

    /**
     * Resolve scope and currency from the selected branch or the default currency.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function resolveDerivedAttributes(array $data): array
    {
        $branchId = blank($data['branch_id'] ?? null) ? null : (int)$data['branch_id'];
        $branch = $branchId ? Branch::query()->find($branchId) : null;

        unset($data['gift_card_batch_id']);

        return [
            ...$data,
            'branch_id' => $branch?->id,
            'scope' => $branch ? GiftCardScope::Branch : GiftCardScope::Global,
            'currency' => $branch?->currency ?? setting('default_currency'),
        ];
    }

    /** @inheritDoc */
    public function update(int|string $id, array $data): GiftCard
    {
        $giftCard = $this->findOrFail($id);
        $giftCard->update($this->resolveDerivedAttributes($data));

        return $giftCard->refresh()->loadMissing(['branch:id,name', 'customer:id,name', 'batch:id,name']);
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return $this->getModel()
            ->query()
            ->whereIn('id', parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();

        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            [
                'key' => 'currency',
                'label' => __('giftcard::gift_cards.filters.currency'),
                'type' => 'select',
                'options' => Currency::supportedList(),
            ],
            [
                'key' => 'scope',
                'label' => __('giftcard::gift_cards.filters.scope'),
                'type' => 'select',
                'options' => GiftCardScope::toArrayTrans(),
            ],
            [
                'key' => 'status',
                'label' => __('giftcard::gift_cards.filters.status'),
                'type' => 'select',
                'options' => GiftCardStatus::toArrayTrans(),
            ],
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
            'statuses' => GiftCardStatus::toArrayTrans(),
            'customers' => User::list(defaultRole: DefaultRole::Customer),
        ];
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function balance(int|string $id): array
    {
        $giftCard = $this->findOrFail($id);

        return [
            'id' => $giftCard->id,
            'uuid' => $giftCard->uuid,
            'code' => $giftCard->code,
            'initial_balance' => $giftCard->initial_balance,
            'current_balance' => $giftCard->current_balance,
            'currency' => $giftCard->currency,
            'status' => $giftCard->status->toTrans(),
            'expiry_date' => $giftCard->expiry_date?->toDateString(),
        ];
    }

    /** @inheritDoc */
    public function convertOrderAmountToGiftCardAmount(
        GiftCard       $giftCard,
        float          $amount,
        string         $orderCurrency,
        float|int|null $orderCurrencyRate,
    ): Money
    {

        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        if ($orderCurrency === $giftCard->currency) {
            return new Money($amount, $giftCard->currency);
        }

        $rate = $orderCurrencyRate ?: CurrencyRate::for($orderCurrency);

        $defaultAmount = (new Money($amount, $orderCurrency))
            ->convertToDefault($rate);

        return $defaultAmount->convert(
            $giftCard->currency,
            CurrencyRate::for($giftCard->currency)
        )->round();
    }
}
