<?php

namespace Modules\Report\Reports\GiftCard;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\Report\Report;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Models\User;

class GiftCardTransactionsReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_transactions';
    }

    /** @inheritDoc */
    public function attributes(): Collection
    {
        return collect([
            'transaction_id',
            'card_code',
            'transaction_type',
            'amount',
            'balance_before',
            'balance_after',
            'currency',
            'branch',
            'order',
            'created_by',
            'date',
        ]);
    }

    /** @inheritDoc */
    public function columns(): array
    {
        return [
            'gift_card_transactions.id',
            'gift_card_transactions.uuid',
            'gift_card_transactions.gift_card_id',
            'gift_card_transactions.type',
            'gift_card_transactions.amount',
            'gift_card_transactions.balance_before',
            'gift_card_transactions.balance_after',
            'gift_card_transactions.branch_id',
            'gift_card_transactions.order_id',
            'gift_card_transactions.created_by',
            'gift_card_transactions.transaction_at',
            'gift_card_transactions.currency',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        $filters = $request->get('filters', []);

        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->whereHas('giftCard', fn(Builder $builder) => $builder
                    ->when(isset($filters['card_code']), fn(Builder $giftCardQuery) => $giftCardQuery->where('code', $filters['card_code']))
                )
                ->with([
                    'giftCard:id,code',
                    'branch:id,name',
                    'order:id,reference_no',
                    'createdBy:id,name',
                ]),
        ];
    }

    /** @inheritDoc */
    public function filters(Request $request): array
    {
        return [
            [
                'key' => 'type',
                'label' => __('giftcard::gift_card_transactions.filters.type'),
                'type' => 'select',
                'options' => GiftCardTransactionType::toArrayTrans(),
            ],
            [
                'key' => 'card_code',
                'label' => __('report::reports.filters.card_code'),
                'type' => 'text',
            ],
            [
                'key' => 'created_by',
                'label' => __('report::reports.filters.created_by'),
                'type' => 'select',
                'options' => User::list()
            ],
            [
                'key' => 'currency',
                'label' => __('giftcard::gift_cards.filters.currency'),
                'type' => 'select',
                'options' => Currency::supportedList(),
            ],
        ];
    }

    /** @inheritDoc */
    public function resource(Model $model): array
    {
        /** @var GiftCardTransaction $model */

        return [
            'transaction_id' => $model->uuid,
            'card_code' => $model->giftCard?->code,
            'transaction_type' => $model->type->trans(),
            'amount' => $model->amount,
            'balance_before' => $model->balance_before,
            'balance_after' => $model->balance_after,
            'currency' => $model->currency,
            'branch' => $model->branch?->name,
            'order' => $model->order?->reference_no,
            'created_by' => $model->createdBy?->name,
            'date' => dateTimeFormat($model->transaction_at),
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
