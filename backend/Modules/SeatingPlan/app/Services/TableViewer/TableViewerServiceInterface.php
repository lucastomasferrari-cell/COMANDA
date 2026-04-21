<?php

namespace Modules\SeatingPlan\Services\TableViewer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\SeatingPlan\Models\Table;
use Throwable;

interface TableViewerServiceInterface
{
    /**
     * Get specific resource
     *
     * @param int $id
     * @return Table|Builder|EloquentCollection|array;
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Table|Builder|EloquentCollection|array;

    /**
     * Display a listing of the resource.
     *
     * @param int|null $branchId
     * @return array
     */
    public function get(?int $branchId = null, array $filters = []): array;

    /**
     * Show the specified resource.
     *
     * @param int $id
     * @return Table
     */
    public function show(int $id): Table;

    /**
     * Assign waiter
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function assignWaiter(int $id, array $data): void;

    /**
     * Make table as available
     *
     * @param int $id
     * @return void
     */
    public function makeAsAvailable(int $id): void;

    /**
     * Merge Tables
     *
     * @param int $id
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public function merge(int $id, array $data): void;

    /**
     * Get merge tables meta
     *
     * @param int $id
     * @return array
     */
    public function getMergeMeta(int $id): array;

    /**
     * Split tables merged
     *
     * @param int $mergeId
     * @return void
     * @throws Throwable
     */
    public function splitTable(int $mergeId): void;
}
