<?php

namespace Modules\Tool\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;
use Modules\Tool\Http\Requests\Api\V1\RestoreDatabaseRequest;
use Modules\Tool\Services\Database\DatabaseServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DatabaseServiceInterface $service
     */
    public function __construct(protected DatabaseServiceInterface $service)
    {
    }

    /**
     * Display database backup/restore page data.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->service->index());
    }

    /**
     * Download backup by file name.
     *
     * @param string $fileName
     * @return BinaryFileResponse
     */
    public function download(string $fileName): BinaryFileResponse
    {
        return $this->service->download($fileName);
    }

    /**
     * Create database backup.
     *
     * @return JsonResponse
     */
    public function backup(): JsonResponse
    {
        return ApiResponse::created(
            body: $this->service->backup(),
            resource: __('tool::database.database'),
            message: __('tool::database.messages.backup_created')
        );
    }

    /**
     * Restore database from uploaded SQL file.
     *
     * @param RestoreDatabaseRequest $request
     * @return JsonResponse
     */
    public function restore(RestoreDatabaseRequest $request): JsonResponse
    {
        return ApiResponse::updated(
            body: $this->service->restore($request->file('file')),
            resource: __('tool::database.database'),
            message: __('tool::database.messages.restore_completed')
        );
    }

    /**
     * Restore database from existing backup file.
     *
     * @param string $fileName
     * @return JsonResponse
     */
    public function restoreFromBackup(string $fileName): JsonResponse
    {
        return ApiResponse::updated(
            body: $this->service->restoreFromBackup($fileName),
            resource: __('tool::database.database'),
            message: __('tool::database.messages.restore_completed')
        );
    }
}
