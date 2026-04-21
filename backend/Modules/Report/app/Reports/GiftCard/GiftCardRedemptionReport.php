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

class GiftCardRedemptionReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_redemption';
    }

    /** @inheritDoc */
    public function attributes(): Collection
    {
        return collect([
            'card_code',
            'order_number',
            'branch',
            'amount_redeemed',
            'currency',
            'exchange_rate',
            'amount_converted',
            'order_currency',
            'redeemed_by',
            'redeemed_at',
        ]);
    }

    /** @inheritDoc */
    public function columns(): array
    {
        return [
            'gift_card_transactions.id',
            'gift_card_transactions.gift_card_id',
            'gift_card_transactions.order_id',
            'gift_card_transactions.created_by',
            'gift_card_transactions.branch_id',
            'gift_card_transactions.amount',
            'gift_card_transactions.currency',
            'gift_card_transactions.exchange_rate',
            'gift_card_transactions.amount_in_order_currency',
            'gift_card_transactions.order_currency',
            'gift_card_transactions.transaction_at',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        $filters = $request->get('filters', []);

        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->where('gift_card_transactions.type', GiftCardTransactionType::Redeem->value)
                ->whereHas(
                    'giftCard',
                    fn(Builder $giftCardQuery) => $giftCardQuery
                        ->when(isset($filters['card_code']), fn(Builder $builder) => $builder->where('code', $filters['card_code']))
                )
                ->when(isset($filters['cashier']), fn(Builder $builder) => $builder->where('created_by', $filters['cashier']))
                ->with([
                    'giftCard:id,code',
                    'branch:id,name',
                    'createdBy:id,name',
                    'order:id,reference_no,currency',
                ]),
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
                'key' => 'cashier',
                'label' => __('report::reports.filters.cashier'),
                'type' => 'select',
                'options' => User::list(),
            ],
            [
                'key' => 'card_code',
                'label' => __('report::reports.filters.card_code'),
                'type' => 'text',
            ],
        ];
    }

    /** @inheritDoc */
    public function resource(Model $model): array
    {
        /** @var GiftCardTransaction $model */

        return [
            'card_code' => $model->giftCard?->code,
            'order_number' => $model->order?->reference_no,
            'branch' => $model->branch?->name,
            'amount_redeemed' => $model->amount,
            'currency' => $model->currency,
            'exchange_rate' => $model->exchange_rate,
            'amount_converted' => $model->amount_in_order_currency,
            'order_currency' => $model->order_currency,
            'redeemed_by' => $model->createdBy?->name,
            'redeemed_at' => dateTimeFormat($model->transaction_at),
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
