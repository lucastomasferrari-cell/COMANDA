<?php

namespace Modules\GiftCard\Services\GiftCardAnalytics;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Branch\Models\Branch;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardBatch;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\Support\GlobalStructureFilters;
use Modules\Support\Money;
use Modules\Support\Enums\Day;

class GiftCardAnalyticsService implements GiftCardAnalyticsServiceInterface
{
    /** @inheritDoc */
    public function section(string $section, array $filters = []): array
    {
        $filters = $this->applyDefaultFilters($filters);

        return match ($section) {
            'overview' => $this->overview($filters),
            'cards' => $this->cards($filters),
            'transactions' => $this->transactions($filters),
            'batches' => $this->batches($filters),
            'branches' => $this->branches($filters),
            'expiry' => $this->expiry($filters),
            'sales_over_time' => $this->salesOverTime($filters),
            'redemption_over_time' => $this->redemptionOverTime($filters),
            'sales_by_branch' => $this->salesByBranch($filters),
            'liability_by_currency' => $this->liabilityByCurrency($filters),
            'usage_by_day_of_week' => $this->usageByDayOfWeek($filters),
            default => $this->analytics($filters),
        };
    }

    /**
     * Normalize filters before building analytics queries.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function applyDefaultFilters(array $filters): array
    {
        $branchId = $this->resolveFilterBranchId($filters);
        $currency = $this->resolveSelectedCurrency($branchId, $filters);

        return [
            ...$filters,
            'branch_id' => $branchId,
            'currency' => $currency,
            'currency_precision' => Currency::subunit($currency),
            'from' => Carbon::parse($filters['from'] ?? now()->startOfYear()->toDateString())->toDateString(),
            'to' => Carbon::parse($filters['to'] ?? now()->toDateString())->toDateString(),
        ];
    }

    /**
     * Resolve effective branch filter from user context.
     *
     * @param array<string,mixed> $filters
     * @return int|null
     */
    protected function resolveFilterBranchId(array $filters): ?int
    {
        $user = auth()->user();

        if ($user->assignedToBranch()) {
            return (int)$user->branch_id;
        }

        if (!empty($filters['branch_id'])) {
            return (int)$filters['branch_id'];
        }

        return $user->effective_branch?->id;
    }

    /**
     * Resolve the analytics currency from branch or explicit filter.
     *
     * @param int|null $branchId
     * @param array<string,mixed> $filters
     * @return string
     */
    protected function resolveSelectedCurrency(?int $branchId, array $filters): string
    {
        if ($branchId) {
            return (string)(Branch::query()
                ->withoutGlobalActive()
                ->whereKey($branchId)
                ->value('currency') ?: setting('default_currency'));
        }

        return (string)($filters['currency'] ?: setting('default_currency'));
    }

    /**
     * Build overview KPI data.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function overview(array $filters): array
    {
        $cards = (clone $this->cardQuery($filters))
            ->selectRaw('COUNT(*) as total_cards')
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_cards", [GiftCardStatus::Active->value])
            ->selectRaw('SUM(CASE WHEN customer_id IS NOT NULL THEN 1 ELSE 0 END) as assigned_cards')
            ->selectRaw('SUM(current_balance) as outstanding_balance_raw')
            ->first();

        $transactions = (clone $this->transactionQuery($filters))
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw("SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as sold_value_raw", [GiftCardTransactionType::Purchase->value])
            ->selectRaw("SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as redeemed_value_raw", [GiftCardTransactionType::Redeem->value])
            ->selectRaw("SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as refunded_value_raw", [GiftCardTransactionType::Refund->value])
            ->first();

        $batches = (clone $this->batchQuery($filters))
            ->selectRaw('COUNT(*) as total_batches')
            ->selectRaw('SUM(quantity) as total_batch_quantity')
            ->selectRaw('SUM(quantity * value) as total_batch_face_value_raw')
            ->first();

        $expiringSoon = (clone $this->cardQuery($filters))
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(30))
            ->count();

        return [
            'currency' => $filters['currency'],
            'currency_precision' => $filters['currency_precision'],
            'total_cards' => (int)($cards?->total_cards ?? 0),
            'active_cards' => (int)($cards?->active_cards ?? 0),
            'assigned_cards' => (int)($cards?->assigned_cards ?? 0),
            'total_transactions' => (int)($transactions?->total_transactions ?? 0),
            'total_batches' => (int)($batches?->total_batches ?? 0),
            'total_batch_quantity' => (int)($batches?->total_batch_quantity ?? 0),
            'expiring_soon_cards' => $expiringSoon,
            'outstanding_balance' => new Money((float)($cards?->outstanding_balance_raw ?? 0), $filters['currency']),
            'sold_value' => new Money((float)($transactions?->sold_value_raw ?? 0), $filters['currency']),
            'redeemed_value' => new Money((float)($transactions?->redeemed_value_raw ?? 0), $filters['currency']),
            'refunded_value' => new Money((float)($transactions?->refunded_value_raw ?? 0), $filters['currency']),
            'batch_face_value' => new Money((float)($batches?->total_batch_face_value_raw ?? 0), $filters['currency']),
        ];
    }

    /**
     * Base card query for analytics.
     *
     * @param array<string,mixed> $filters
     * @return Builder<GiftCard>
     */
    protected function cardQuery(array $filters): Builder
    {
        return GiftCard::query()
            ->when(!empty($filters['branch_id']), fn(Builder $query) => $query->where('gift_cards.branch_id', (int)$filters['branch_id']))
            ->when(!empty($filters['currency']), fn(Builder $query) => $query->where('gift_cards.currency', $filters['currency']))
            ->when(!empty($filters['from']), fn(Builder $query) => $query->whereDate('gift_cards.created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn(Builder $query) => $query->whereDate('gift_cards.created_at', '<=', $filters['to']));
    }

    /**
     * Base transaction query for analytics.
     *
     * @param array<string,mixed> $filters
     * @return Builder<GiftCardTransaction>
     */
    protected function transactionQuery(array $filters): Builder
    {
        return GiftCardTransaction::query()
            ->whereHas('giftCard')
            ->when(!empty($filters['branch_id']), fn(Builder $query) => $query->where('gift_card_transactions.branch_id', (int)$filters['branch_id']))
            ->when(!empty($filters['currency']), fn(Builder $query) => $query->where('gift_card_transactions.currency', $filters['currency']))
            ->when(!empty($filters['from']), fn(Builder $query) => $query->whereDate('gift_card_transactions.transaction_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn(Builder $query) => $query->whereDate('gift_card_transactions.transaction_at', '<=', $filters['to']));
    }

    /**
     * Base batch query for analytics.
     *
     * @param array<string,mixed> $filters
     * @return Builder<GiftCardBatch>
     */
    protected function batchQuery(array $filters): Builder
    {
        return GiftCardBatch::query()
            ->when(!empty($filters['branch_id']), fn(Builder $query) => $query->where('gift_card_batches.branch_id', (int)$filters['branch_id']))
            ->when(!empty($filters['currency']), fn(Builder $query) => $query->where('gift_card_batches.currency', $filters['currency']))
            ->when(!empty($filters['from']), fn(Builder $query) => $query->whereDate('gift_card_batches.created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn(Builder $query) => $query->whereDate('gift_card_batches.created_at', '<=', $filters['to']));
    }

    /**
     * Build card-centric analytics.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function cards(array $filters): array
    {
        $baseQuery = $this->cardQuery($filters);

        $statusMix = (clone $baseQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as cards_count')
            ->groupBy('status')
            ->get()
            ->map(fn($row) => [
                'key' => $row->status,
                'label' => $row->status->trans(),
                'count' => (int)$row->cards_count,
            ]);

        $scopeMix = (clone $baseQuery)
            ->select('scope')
            ->selectRaw('COUNT(*) as cards_count')
            ->groupBy('scope')
            ->get()
            ->map(fn($row) => [
                'key' => $row->scope,
                'label' => $row->scope->trans(),
                'count' => (int)$row->cards_count,
            ]);

        $topBalances = (clone $baseQuery)
            ->with(['branch:id,name', 'customer:id,name'])
            ->orderByDesc('current_balance')
            ->limit(6)
            ->get()
            ->map(fn(GiftCard $giftCard) => [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'branch' => $giftCard->branch?->name,
                'customer' => $giftCard->customer?->name,
                'status' => $giftCard->status->value,
                'status_label' => $giftCard->status->toTrans(),
                'scope' => $giftCard->scope->value,
                'scope_label' => $giftCard->scope->toTrans(),
                'current_balance' => $giftCard->current_balance,
            ]);

        return [
            'status_mix' => $statusMix,
            'scope_mix' => $scopeMix,
            'top_balances' => $topBalances,
        ];
    }

    /**
     * Build transaction analytics.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function transactions(array $filters): array
    {
        $baseQuery = $this->transactionQuery($filters);

        $typeBreakdown = (clone $baseQuery)
            ->select('type')
            ->selectRaw('COUNT(*) as transactions_count')
            ->selectRaw('SUM(amount) as total_amount_raw')
            ->groupBy('type')
            ->orderByDesc('transactions_count')
            ->get()
            ->map(fn($row) => [
                'key' => $row->type,
                'label' => $row->type->trans(),
                'count' => (int)$row->transactions_count,
                'amount' => new Money((float)$row->total_amount_raw, $filters['currency']),
            ]);

        $dailyTrend = (clone $baseQuery)
            ->selectRaw('DATE(transaction_at) as day')
            ->selectRaw('COUNT(*) as transactions_count')
            ->selectRaw('SUM(amount) as total_amount_raw')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($row) => [
                'day' => $row->day,
                'count' => (int)$row->transactions_count,
                'amount' => new Money((float)$row->total_amount_raw, $filters['currency']),
            ]);

        $recentTransactions = (clone $baseQuery)
            ->with(['giftCard:id,code', 'branch:id,name'])
            ->latest('transaction_at')
            ->limit(8)
            ->get()
            ->map(fn(GiftCardTransaction $transaction) => [
                'id' => $transaction->id,
                'uuid' => $transaction->uuid,
                'code' => $transaction->giftCard?->code,
                'type' => $transaction->type->value,
                'type_label' => $transaction->type->toTrans(),
                'amount' => $transaction->amount,
                'branch' => $transaction->branch?->name,
                'transaction_at' => $transaction->transaction_at?->format('Y-m-d H:i'),
            ]);

        return [
            'type_breakdown' => $typeBreakdown,
            'daily_trend' => $dailyTrend,
            'recent_transactions' => $recentTransactions,
        ];
    }

    /**
     * Build batch analytics.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function batches(array $filters): array
    {
        $summary = (clone $this->batchQuery($filters))
            ->selectRaw('COUNT(*) as total_batches')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(quantity * value) as total_value_raw')
            ->first();

        $topBatches = (clone $this->batchQuery($filters))
            ->with('branch:id,name')
            ->withCount('cards as cards_generated')
            ->withCount([
                'cards as cards_used' => fn(Builder $query) => $query->where('status', GiftCardStatus::Used->value),
            ])
            ->orderByDesc(DB::raw('quantity * value'))
            ->limit(6)
            ->get()
            ->map(fn(GiftCardBatch $batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'branch' => $batch->branch?->name,
                'quantity' => $batch->quantity,
                'value' => $batch->value,
                'cards_generated' => (int)$batch->cards_generated,
                'cards_used' => (int)$batch->cards_used,
                'cards_remaining' => max((int)$batch->cards_generated - (int)$batch->cards_used, 0),
            ]);

        return [
            'summary' => [
                'total_batches' => (int)($summary?->total_batches ?? 0),
                'total_quantity' => (int)($summary?->total_quantity ?? 0),
                'total_value' => new Money((float)($summary?->total_value_raw ?? 0), $filters['currency']),
            ],
            'top_batches' => $topBatches,
        ];
    }

    /**
     * Build branch comparison analytics.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function branches(array $filters): array
    {
        $currency = $filters['currency'];
        if (!auth()->user()?->assignedToBranch()) {
            unset($filters['branch_id']);
            unset($filters['currency']);
        }

        $soldSubQuery = (clone $this->transactionQuery($filters))
            ->where('type', GiftCardTransactionType::Purchase->value)
            ->select('branch_id')
            ->selectRaw('SUM(amount) as sold_value_raw')
            ->groupBy('branch_id');

        $redeemedSubQuery = (clone $this->transactionQuery($filters))
            ->where('type', GiftCardTransactionType::Redeem->value)
            ->select('branch_id')
            ->selectRaw('SUM(amount) as redeemed_value_raw')
            ->groupBy('branch_id');

        $rows = (clone $this->cardQuery($filters))
            ->leftJoin('branches', 'branches.id', '=', 'gift_cards.branch_id')
            ->leftJoinSub($soldSubQuery, 'gift_card_sold', fn($join) => $join->on('gift_card_sold.branch_id', '=', 'gift_cards.branch_id'))
            ->leftJoinSub($redeemedSubQuery, 'gift_card_redeemed', fn($join) => $join->on('gift_card_redeemed.branch_id', '=', 'gift_cards.branch_id'))
            ->selectRaw('gift_cards.branch_id')
            ->selectRaw("COALESCE(NULLIF(branches.name, ''), ?) as branch_name", [__('giftcard::gift_cards.analytics.global_pool')])
            ->selectRaw('COUNT(gift_cards.id) as cards_count')
            ->selectRaw('SUM(gift_cards.current_balance) as outstanding_balance_raw')
            ->selectRaw('COALESCE(MAX(gift_card_sold.sold_value_raw), 0) as sold_value_raw')
            ->selectRaw('COALESCE(MAX(gift_card_redeemed.redeemed_value_raw), 0) as redeemed_value_raw')
            ->groupBy('gift_cards.branch_id', 'branches.name')
            ->orderByDesc('outstanding_balance_raw')
            ->limit(8)
            ->get();

        return [
            'highlights' => $rows->map(fn($row) => [
                'branch_id' => $row->branch_id,
                'branch_name' => $row->branch_name ?: __('giftcard::gift_cards.analytics.global_pool'),
                'cards_count' => (int)$row->cards_count,
                'outstanding_balance' => new Money((float)$row->outstanding_balance_raw, $currency),
                'sold_value' => new Money((float)$row->sold_value_raw, $currency),
                'redeemed_value' => new Money((float)$row->redeemed_value_raw, $currency),
            ])
        ];
    }

    /**
     * Build expiry monitoring analytics.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function expiry(array $filters): array
    {
        $baseQuery = $this->cardQuery($filters)
            ->whereIn('status', [GiftCardStatus::Active->value, GiftCardStatus::Used->value])
            ->whereNotNull('expiry_date');

        $expiringIn7Days = (clone $baseQuery)
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(7))
            ->count();

        $expiringIn30Days = (clone $baseQuery)
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(30))
            ->count();

        $expiredCount = (clone $this->cardQuery($filters))
            ->where('status', GiftCardStatus::Expired->value)
            ->count();

        $cards = (clone $baseQuery)
            ->with(['branch:id,name', 'customer:id,name'])
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(30))
            ->orderBy('expiry_date')
            ->limit(8)
            ->get()
            ->map(fn(GiftCard $giftCard) => [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'branch' => $giftCard->branch?->name,
                'customer' => $giftCard->customer?->name,
                'expiry_date' => $giftCard->expiry_date?->toDateString(),
                'status' => $giftCard->status->value,
                'status_label' => $giftCard->status->toTrans(),
                'current_balance' => $giftCard->current_balance,
            ]);

        return [
            'summary' => [
                'expiring_in_7_days' => $expiringIn7Days,
                'expiring_in_30_days' => $expiringIn30Days,
                'expired_cards' => $expiredCount,
            ],
            'cards' => $cards,
        ];
    }

    /**
     * Build gift card sales over time line-chart data.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function salesOverTime(array $filters): array
    {
        return $this->buildTransactionTimeSeries($filters, GiftCardTransactionType::Purchase);
    }

    /**
     * Build transaction time-series chart data.
     *
     * @param array<string,mixed> $filters
     * @param GiftCardTransactionType $type
     * @return array<string,mixed>
     */
    protected function buildTransactionTimeSeries(array $filters, GiftCardTransactionType $type): array
    {
        $rows = (clone $this->transactionQuery($filters))
            ->where('type', $type->value)
            ->selectRaw('DATE(transaction_at) as day')
            ->selectRaw('SUM(amount) as total_amount_raw')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $period = collect(Carbon::parse($filters['from'])->daysUntil(Carbon::parse($filters['to'])->addDay()))
            ->map(fn(Carbon $date) => $date->toDateString());

        return [
            'labels' => $period->map(fn(string $day) => Carbon::parse($day)->format('M d')),
            'series' => $period->map(fn(string $day) => round((float)($rows->get($day)?->total_amount_raw ?? 0), $filters['currency_precision'])),
            'currency' => $filters['currency'],
            'precision' => $filters['currency_precision'],
        ];
    }

    /**
     * Build gift card redemption over time line-chart data.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function redemptionOverTime(array $filters): array
    {
        return $this->buildTransactionTimeSeries($filters, GiftCardTransactionType::Redeem);
    }

    /**
     * Build gift card sales by branch bar-chart data.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function salesByBranch(array $filters): array
    {
        $rows = (clone $this->transactionQuery($filters))
            ->where('type', GiftCardTransactionType::Purchase->value)
            ->leftJoin('branches', 'branches.id', '=', 'gift_card_transactions.branch_id')
            ->selectRaw('gift_card_transactions.branch_id')
            ->selectRaw("COALESCE(NULLIF(branches.name, ''), ?) as branch_name", [__('giftcard::gift_cards.analytics.global_pool')])
            ->selectRaw('SUM(gift_card_transactions.amount) as total_amount_raw')
            ->groupBy('gift_card_transactions.branch_id', 'branches.name')
            ->orderByDesc('total_amount_raw')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('branch_name'),
            'series' => $rows->map(fn($row) => round((float)$row->total_amount_raw, $filters['currency_precision'])),
            'currency' => $filters['currency'],
            'precision' => $filters['currency_precision'],
        ];
    }

    /**
     * Build outstanding liability by currency donut-chart data.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function liabilityByCurrency(array $filters): array
    {
        $rows = (clone $this->cardQuery($filters))
            ->select('currency')
            ->selectRaw('SUM(current_balance) as total_amount_raw')
            ->groupBy('currency')
            ->orderByDesc('total_amount_raw')
            ->get();

        return [
            'labels' => $rows->pluck('currency'),
            'series' => $rows->map(fn($row) => round((float)$row->total_amount_raw, $filters['currency_precision'])),
        ];
    }

    /**
     * Build day-of-week usage bar-chart data from redemption transactions.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    protected function usageByDayOfWeek(array $filters): array
    {
        $driver = DB::connection()->getDriverName();
        $weekDayExpression = $driver === 'pgsql'
            ? 'EXTRACT(DOW FROM transaction_at) + 1'
            : 'DAYOFWEEK(transaction_at)';

        $rows = (clone $this->transactionQuery($filters))
            ->where('type', GiftCardTransactionType::Redeem->value)
            ->selectRaw("$weekDayExpression as week_day")
            ->selectRaw('COUNT(*) as usage_count')
            ->groupBy('week_day')
            ->get()
            ->keyBy('week_day');

        $weekDays = collect([
            1 => __('giftcard::gift_cards.analytics.days.sunday'),
            2 => __('giftcard::gift_cards.analytics.days.monday'),
            3 => __('giftcard::gift_cards.analytics.days.tuesday'),
            4 => __('giftcard::gift_cards.analytics.days.wednesday'),
            5 => __('giftcard::gift_cards.analytics.days.thursday'),
            6 => __('giftcard::gift_cards.analytics.days.friday'),
            7 => __('giftcard::gift_cards.analytics.days.saturday'),
        ]);

        $dayIndexes = [
            Day::Sunday->value => 1,
            Day::Monday->value => 2,
            Day::Tuesday->value => 3,
            Day::Wednesday->value => 4,
            Day::Thursday->value => 5,
            Day::Friday->value => 6,
            Day::Saturday->value => 7,
        ];

        $orderedDayKeys = collect(array_keys($dayIndexes));
        $startDay = (string)setting('start_of_week', Day::Sunday->value);
        $endDay = (string)setting('end_of_week', Day::Saturday->value);
        $startIndex = $orderedDayKeys->search($startDay);
        $endIndex = $orderedDayKeys->search($endDay);

        if ($startIndex === false) {
            $startIndex = 0;
        }

        if ($endIndex === false) {
            $endIndex = $orderedDayKeys->count() - 1;
        }

        $orderedDayKeys = $orderedDayKeys
            ->slice($startIndex)
            ->concat($orderedDayKeys->take($startIndex));

        if ($orderedDayKeys->contains($endDay)) {
            $endPosition = $orderedDayKeys->search($endDay);
            $orderedDayKeys = $orderedDayKeys
                ->take($endPosition + 1)
                ->concat($orderedDayKeys->slice($endPosition + 1));
        }

        return [
            'labels' => $orderedDayKeys->map(fn(string $day) => $weekDays->get($dayIndexes[$day]))->values()->all(),
            'series' => $orderedDayKeys->map(fn(string $day) => (int)($rows->get($dayIndexes[$day])?->usage_count ?? 0))->values()->all(),
        ];
    }

    /** @inheritDoc */
    public function analytics(array $filters = []): array
    {
        $filters = $this->applyDefaultFilters($filters);

        return [
            'overview' => $this->overview($filters),
            'cards' => $this->cards($filters),
            'transactions' => $this->transactions($filters),
            'batches' => $this->batches($filters),
            'branches' => $this->branches($filters),
            'expiry' => $this->expiry($filters),
            'sales_over_time' => $this->salesOverTime($filters),
            'redemption_over_time' => $this->redemptionOverTime($filters),
            'sales_by_branch' => $this->salesByBranch($filters),
            'liability_by_currency' => $this->liabilityByCurrency($filters),
            'usage_by_day_of_week' => $this->usageByDayOfWeek($filters),
        ];
    }

    /** @inheritDoc */
    public function filters(array $filters = []): array
    {
        $branchId = $this->resolveFilterBranchId($filters);
        $currency = $this->resolveSelectedCurrency($branchId, $filters);

        $branchFilter = GlobalStructureFilters::branch();

        return [
            'filters' => [
                ...(is_null($branchFilter) ? [] : [$branchFilter]),
                GlobalStructureFilters::from(),
                GlobalStructureFilters::to(),
            ],
            'default_filters' => [
                'branch_id' => $branchId,
                'currency' => $currency,
                'currency_precision' => Currency::subunit($currency),
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
            ],
        ];
    }
}
