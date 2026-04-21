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

class GiftCardExpiryReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_expiry';
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
            'remaining_balance',
            'currency',
            'expiry_date',
            'status',
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
            'gift_cards.expiry_date',
            'gift_cards.status',
            'gift_cards.currency',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->whereNotNull('expiry_date')
                ->with(['branch:id,name', 'customer:id,name']),
        ];
    }

    /** @inheritDoc */
    public function filters(Request $request): array
    {
        return [
            [
                'key' => 'expiry_from',
                'label' => __('report::reports.filters.expiry_from'),
                'type' => 'date',
            ],
            [
                'key' => 'expiry_to',
                'label' => __('report::reports.filters.expiry_to'),
                'type' => 'date',
            ],
            [
                'key' => 'status',
                'label' => __('giftcard::gift_cards.filters.status'),
                'type' => 'select',
                'options' => GiftCardStatus::toArrayTrans(),
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
        /** @var GiftCard $model */

        return [
            'card_code' => $model->code,
            'branch' => $model->branch?->name,
            'customer' => $model->customer?->name,
            'initial_balance' => $model->initial_balance,
            'remaining_balance' => $model->current_balance,
            'currency' => $model->currency,
            'expiry_date' => dateTimeFormat($model->expiry_date, DateTimeFormat::Date),
            'status' => $model->status->trans(),
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
