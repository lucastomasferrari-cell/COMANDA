<?php

namespace Modules\Report;

use App\Forkiva;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Modules\Branch\Models\Branch;
use Modules\Report\Contracts\ReportInterface;
use Modules\Report\Traits\HasExportReport;
use Modules\Support\Enums\GroupDateType;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Models\User;

abstract class Report implements ReportInterface
{
    use HasExportReport;

    /**
     * User Assigned branch
     *
     * @var Branch
     */
    public Branch $branch;

    /**
     * Determine if you use currency_rate or not
     *
     * @var bool
     */
    public bool $withRate = false;

    /**
     * Authentication user
     *
     * @var User|null
     */
    public ?User $user = null;

    /**
     * Use currency
     *
     * @var string
     */
    public string $currency;

    /**
     * Create a new instance of Report
     */
    public function __construct()
    {
        $this->user = auth()->user();

        if ($this->user->assignedToBranch()) {
            $this->branch = $this->user->branch;
            $this->currency = $this->branch->currency;
        } else {
            $this->currency = setting("default_currency");
            $this->withRate = true;
        }
    }

    /** @inheritDoc */
    public function permission(): string
    {
        return "admin.reports.{$this->key()}";
    }

    /** @inheritDoc */
    public function transAttributes(): array
    {
        return $this->attributes()
            ->mapWithKeys(fn($attribute, $key) => [
                $key => __("report::attributes.{$this->key()}.$attribute")
            ])
            ->toArray();
    }

    /** @inheritDoc */
    public function render(Request $request): array
    {
        return [
            "key" => $this->key(),
            "label" => $this->transLabel(),
            "filters" => [
                ...$this->globalFilters(),
                ...$this->filters($request)
            ],
            "headers" => $this->attributes()
                ->map(fn($attribute) => [
                    "title" => __("report::attributes.{$this->key()}.$attribute"),
                    "value" => $attribute,
                    "sortable" => false,
                ]),
            "export_methods" => $this->exportMethods(),
            "has_search" => $this->hasSearch(),
            ...$this->data($request),
        ];
    }

    /** @inheritDoc */
    public function transLabel(): string
    {
        return __($this->label());
    }

    /** @inheritDoc */
    public function label(): string
    {
        return "report::reports.definitions.{$this->key()}.title";
    }

    /**
     * Get global filters
     *
     * @return array
     */
    public function globalFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();

        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            GlobalStructureFilters::from(__("report::reports.filters.start_date")),
            GlobalStructureFilters::to(__("report::reports.filters.end_date")),
            GlobalStructureFilters::groupByDate()
        ];
    }

    /** @inheritDoc */
    public function filters(Request $request): array
    {
        return [];
    }

    /** @inheritDoc */
    public function hasSearch(): bool
    {
        return false;
    }

    /** @inheritDoc */
    public function data(Request $request, bool $withPagination = true): array|Collection
    {
        $this->resolveDefaultDateColumn();
        $query = app(Pipeline::class)
            ->send($this->model()::query())
            ->through($this->through($request))
            ->thenReturn()
            ->with($this->with())
            ->selectRaw(implode(",", $this->columns()))
            ->latest($this->model()::$defaultDateColumn ?? 'created_at')
            ->filters($request->get('filters', []));

        if ($withPagination) {
            $data = $query->paginate(Forkiva::paginate())->withQueryString();

            return [
                'data' => array_map(fn($item) => $this->resource($item), $data->items()),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'from' => $data->firstItem(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'to' => $data->lastItem(),
                    'total' => $data->total(),
                ]
            ];
        } else {
            return $query->get()->map(fn($item) => $this->resource($item));
        }
    }

    /**
     * Resolve default date column
     * @return void
     */
    protected function resolveDefaultDateColumn(): void
    {

    }

    /** @inheritDoc */
    public function with(): array
    {
        return [];
    }

    /**
     * Determine if user has filter in group by date
     *
     * @param Request $request
     * @return bool
     */
    protected function hasGroupByData(Request $request): bool
    {
        return isset($request->get('filters', [])['group_by_date']) && in_array($request->get('filters', [])['group_by_date'], GroupDateType::values());
    }
}
