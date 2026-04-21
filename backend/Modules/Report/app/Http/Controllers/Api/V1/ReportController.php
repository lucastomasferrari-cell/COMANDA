<?php

namespace Modules\Report\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Report\Enums\ExportMethod;
use Modules\Report\Services\Report\ReportServiceInterface;
use Modules\Support\ApiResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Create a new instance of ReportController
     *
     * @param ReportServiceInterface $service
     */
    public function __construct(protected ReportServiceInterface $service)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string $report
     * @return JsonResponse
     */
    public function index(Request $request, string $report): JsonResponse
    {
        return ApiResponse::success($this->service->renderReport($request, $report));
    }

    /**
     * Export report
     *
     * @param Request $request
     * @param string $report
     * @param ExportMethod $method
     * @return StreamedResponse|BinaryFileResponse
     */
    public function export(Request $request, string $report, ExportMethod $method): StreamedResponse|BinaryFileResponse
    {
        return $this->service->export($request, $report, $method);
    }
}
