<?php

namespace Modules\ActivityLog\Services\ActivityLog;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Models\ActivityLog;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Models\User;

class ActivityLogService implements ActivityLogServiceInterface
{
    /** @inheritDoc */
    public function show(int $id): ActivityLog
    {
        return ActivityLog::query()->with(["subject", "causer"])->findOrFail($id);
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $data = DB::table('activity_log as a')
            ->select(
                'a.event',
                'a.log_name',
                'u.id as user_id',
                'u.name as user_name'
            )
            ->leftJoin('users as u', function ($join) {
                $join->on('a.causer_id', '=', 'u.id')
                    ->where('a.causer_type', '=', User::class);
            })
            ->distinct()
            ->get();

        return [
            [
                "key" => 'ip',
                "label" => __('activitylog::activity_logs.filters.ip'),
                "type" => 'text',
            ],
            [
                "key" => 'batch_uuid',
                "label" => __('activitylog::activity_logs.filters.batch_uuid'),
                "type" => 'text',
            ],
            [
                "key" => 'causer_user',
                "label" => __('activitylog::activity_logs.filters.causer_user'),
                "type" => 'select',
                "options" => $data->map(fn($d) => ['id' => $d->user_id, 'name' => $d->user_name])
                    ->filter(fn($u) => $u['id'] !== null)
                    ->unique('id')->values(),
            ],
            [
                "key" => 'event',
                "label" => __('activitylog::activity_logs.filters.event'),
                "type" => 'select',
                'multiple' => true,
                "options" => $data->pluck('event')
                    ->filter()
                    ->unique()
                    ->map(function ($item) {
                        return [
                            'id' => $item,
                            'name' => __("activitylog::activity_logs.events.$item"),
                        ];
                    })->values(),
            ],
            [
                "key" => 'log_name',
                "label" => __('activitylog::activity_logs.filters.log_name'),
                "type" => 'select',
                'multiple' => true,
                "options" => $data->pluck('log_name')
                    ->filter()
                    ->unique()
                    ->map(fn($item) => ['id' => $item, 'name' => $item])->values(),
            ],
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->with(["causer"])
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate());
    }
}
