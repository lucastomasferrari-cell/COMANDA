<?php

namespace Modules\Report\Reports\GiftCard;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Models\GiftCard;
use Modules\Report\Report;
use Modules\Support\Enums\DateTimeFormat;
use Modules\Support\GlobalStructureFilters;

class GiftCardOutstandingBalanceReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_outstanding_balance';
    }

    /** @inheritDoc */
    public function model(): string
    {
        return GiftCard::class;
    }

    /** @inheritDoc */
    public function attributes(): Collection
    {
        return collect([
            'card_code',
            'branch',
            'customer',
            'initial_balance',
            'current_balance',
            'currency',
            'status',
            'expiry_date',
            'created_at',
        ]);
    }

    /** @inheritDoc */
    public function columns(): array
    {
        return [
            'gift_cards.id',
            'gift_cards.code',
            'gift_cards.branch_id',
            'gift_cards.customer_id',
            'gift_cards.initial_balance',
            'gift_cards.current_balance',
            'gift_cards.currency',
            'gift_cards.status',
            'gift_cards.expiry_date',
            'gift_cards.created_at',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->with(['branch:id,name', 'customer:id,name']),
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
                'key' => 'status',
                'label' => __('giftcard::gift_cards.filters.status'),
                'type' => 'select',
                'options' => GiftCardStatus::toArrayTrans(),
            ],
        ];
    }

    /** @inheritDoc */
    public function resource(Model $model): array
    {
        /** @var GiftCard $model */

        return [
            'card_code' => $model->code,
            'branch' => $model->branch?->name,
            'customer' => $model->customer?->name,
            'initial_balance' => $model->initial_balance,
            'current_balance' => $model->current_balance,
            'currency' => $model->currency,
            'status' => $model->status->trans(),
            'expiry_date' => dateTimeFormat($model->expiry_date, DateTimeFormat::Date),
            'created_at' => dateTimeFormat($model->created_at),
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
}
