<?php

namespace Modules\Report\Reports\GiftCard;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Models\GiftCardBatch;
use Modules\Report\Report;
use Modules\Support\GlobalStructureFilters;

class GiftCardBatchReport extends Report
{
    /** @inheritDoc */
    public function key(): string
    {
        return 'gift_card_batch';
    }

    /** @inheritDoc */
    public function model(): string
    {
        return GiftCardBatch::class;
    }

    /** @inheritDoc */
    public function attributes(): Collection
    {
        return collect([
            'batch_name',
            'branch',
            'cards_generated',
            'total_value',
            'currency',
            'cards_used',
            'cards_remaining',
            'created_by',
            'created_at',
        ]);
    }

    /** @inheritDoc */
    public function columns(): array
    {
        return [
            'gift_card_batches.id',
            'gift_card_batches.name',
            'gift_card_batches.quantity',
            'gift_card_batches.value',
            'gift_card_batches.currency',
            'gift_card_batches.branch_id',
            'gift_card_batches.created_by',
            'gift_card_batches.created_at',
        ];
    }

    /** @inheritDoc */
    public function through(Request $request): array
    {
        $filters = $request->get('filters', []);

        return [
            fn(Builder $query, Closure $next) => $next($query)
                ->when(isset($filters['batch_name']), fn(Builder $builder) => $builder->whereKey($filters['batch_name']))
                ->with(['branch:id,name', 'createdBy:id,name'])
                ->withCount([
                    'cards as cards_generated',
                    'cards as cards_used' => fn($builder) => $builder->whereIn('status', [
                        GiftCardStatus::Used->value,
                        GiftCardStatus::Expired->value,
                    ]),
                ]),
        ];
    }


    /** @inheritDoc */
    public function resource(Model $model): array
    {
        /** @var GiftCardBatch $model */
        $cardsGenerated = (int)($model->cards_generated ?? 0);
        $cardsUsed = (int)($model->cards_used ?? 0);

        return [
            'batch_name' => $model->name,
            'branch' => $model->branch?->name,
            'cards_generated' => $cardsGenerated,
            'total_value' => $model->value->multiply($model->quantity),
            'currency' => $model->currency,
            'cards_used' => $cardsUsed,
            'cards_remaining' => max($cardsGenerated - $cardsUsed, 0),
            'created_by' => $model->createdBy?->name,
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
