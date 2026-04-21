<?php

namespace Modules\Report\Reports\GiftCard;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\Report\Report;
use Modules\Support\Enums\DateTimeFormat;
use Modules\Support\Money;

class GiftCardBranchPerformanceReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_branch_performance';
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
            'period',
            'branch',
            'currency',
            'cards_sold',
            'total_sold_value',
            'redeemed_value',
            'outstanding_balance',
            'expired_balance',
        ]);
    }

    /** @inheritDoc */
    public function columns(): array
    {
        return [
            "MIN(created_at) as start_date",
            "MAX(created_at) as end_date",
            'gift_cards.branch_id',
            'gift_cards.currency',
            'COUNT(*) as cards_sold',
            'SUM(gift_cards.initial_balance) as total_sold_value_raw',
            'SUM(gift_cards.current_balance) as outstanding_balance_raw',
            'COALESCE(gift_card_transaction_sums.redeemed_value_raw, 0) as redeemed_value_raw',
            'COALESCE(gift_card_transaction_sums.expired_balance_raw, 0) as expired_balance_raw',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        $transactionSums = GiftCardTransaction::query()
            ->selectRaw(
                'branch_id, currency,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as redeemed_value_raw,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as expired_balance_raw',
                [
                    GiftCardTransactionType::Redeem->value,
                    GiftCardTransactionType::Expire->value,
                ]
            )
            ->groupBy('branch_id', 'currency');

        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->leftJoinSub($transactionSums, 'gift_card_transaction_sums', function ($join) {
                    $join->on('gift_card_transaction_sums.branch_id', '=', 'gift_cards.branch_id')
                        ->on('gift_card_transaction_sums.currency', '=', 'gift_cards.currency');
                })
                ->whereHas('transactions', fn(Builder $builder) => $builder->where('type', GiftCardTransactionType::Purchase->value))
                ->with('branch:id,name')
                ->groupBy(
                    'gift_cards.branch_id',
                    'gift_cards.currency',
                    'gift_card_transaction_sums.redeemed_value_raw',
                    'gift_card_transaction_sums.expired_balance_raw',
                ),
        ];
    }

    /** @inheritDoc */
    public function resource(Model $model): array
    {
        /** @var GiftCard $model */

        return [
            "period" => dateTimeFormat($model->start_date, DateTimeFormat::Date) . " - " . dateTimeFormat($model->end_date, DateTimeFormat::Date),
            'branch' => $model->branch?->name ?? __('admin::sidebar.branches'),
            'currency' => $model->currency,
            'cards_sold' => (int)$model->cards_sold,
            'total_sold_value' => new Money($model->total_sold_value_raw, $model->currency),
            'redeemed_value' => new Money($model->redeemed_value_raw, $model->currency),
            'outstanding_balance' => new Money($model->outstanding_balance_raw, $model->currency),
            'expired_balance' => new Money($model->expired_balance_raw, $model->currency),
        ];
    }
}
