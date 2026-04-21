<?php

namespace Modules\Report\Reports\GiftCard;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Models\GiftCardBatch;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\Report\Report;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Models\User;

class GiftCardSalesReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_sales';
    }

    /** @inheritDoc */
    public function attributes(): Collection
    {
        return collect([
            'card_code',
            'batch',
            'branch',
            'customer',
            'initial_balance',
            'sold_by',
            'sold_at',
        ]);
    }

    /** @inheritDoc */
    public function columns(): array
    {
        return [
            'gift_card_transactions.id',
            'gift_card_transactions.gift_card_id',
            'gift_card_transactions.created_by',
            'gift_card_transactions.amount',
            'gift_card_transactions.currency',
            'gift_card_transactions.transaction_at',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->where('gift_card_transactions.type', GiftCardTransactionType::Purchase->value)
                ->whereHas('giftCard')
                ->with([
                    'giftCard.branch:id,name',
                    'giftCard.batch:id,name',
                    'giftCard.customer:id,name',
                    'createdBy:id,name',
                ])
        ];
    }

    /** @inheritDoc */
    public function filters(Request $request): array
    {
        return [
            [
                'key' => 'currency',
                'label' => __('giftcard::gift_cards.filters.currency'),
                'type' => 'select',
                'options' => Currency::supportedList(),
            ],
            [
                'key' => 'created_by',
                'label' => __('report::reports.filters.sold_by'),
                'type' => 'select',
                'options' => User::list(),
            ],
            [
                'key' => 'branch_id',
                'label' => __('giftcard::attributes.gift_cards.gift_card_batch_id'),
                'type' => 'select',
                'options' => GiftCardBatch::list(),
            ],
        ];
    }

    /** @inheritDoc */
    public function resource(Model $model): array
    {
        /** @var GiftCardTransaction $model */
        return [
            'card_code' => $model->giftCard?->code,
            'batch' => $model->giftCard?->batch?->name,
            'branch' => $model->giftCard?->branch?->name,
            'customer' => $model->giftCard?->customer?->name,
            'initial_balance' => $model->amount,
            'currency' => $model->currency,
            'sold_by' => $model->createdBy?->name,
            'sold_at' => dateTimeFormat($model->transaction_at),
        ];
    }

    /** @inheritDoc */
    public function globalFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();

        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            GlobalStructureFilters::from(__("report::reports.filters.start_date")),
            GlobalStructureFilters::to(__("report::reports.filters.end_date")),
        ];
    }

    /** @inheritDoc */
    protected function resolveDefaultDateColumn(): void
    {
        $this->model()::$defaultDateColumn = 'gift_card_transactions.transaction_at';
    }

    /** @inheritDoc */
    public function model(): string
    {
        return GiftCardTransaction::class;
    }
}
