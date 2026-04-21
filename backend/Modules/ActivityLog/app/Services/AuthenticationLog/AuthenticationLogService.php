<?php

namespace Modules\ActivityLog\Services\AuthenticationLog;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\ActivityLog\Models\AuthenticationLog;

class AuthenticationLogService implements AuthenticationLogServiceInterface
{
    /** @inheritDoc */
    public function get(array $filters, ?array $sorts = []): LengthAwarePaginator
    {
        return AuthenticationLog::query()
            ->filters($filters)
            ->sortBy($sorts)
            ->with(["authenticatable"])
            ->paginate(Forkiva::paginate());
    }

    /** @inheritDoc */
    public function show(int $id): AuthenticationLog
    {
        return AuthenticationLog::query()->with(["authenticatable"])->findOrFail($id);
    }
}
