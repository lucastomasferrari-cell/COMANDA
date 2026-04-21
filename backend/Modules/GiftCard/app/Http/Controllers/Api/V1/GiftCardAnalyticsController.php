<?php

namespace Modules\GiftCard\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\GiftCard\Services\GiftCardAnalytics\GiftCardAnalyticsServiceInterface;
use Modules\Support\ApiResponse;

class GiftCardAnalyticsController extends Controller
{
    /**
     * Create a new instance of GiftCardAnalyticsController.
     */
    public function __construct(protected GiftCardAnalyticsServiceInterface $service)
    {
    }

    /**
     * Return gift card analytics filter schema and defaults.
     */
    public function filters(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->filters($request->get('filters', []))
        );
    }

    /**
     * Return gift card analytics payload or one requested section.
     */
    public function index(Request $request): JsonResponse
    {
        $section = $request->get('section', '');
        $filters = $request->get('filters', []);

        if (!empty($section)) {
            return ApiResponse::success($this->service->section($section, $filters));
        }

        return ApiResponse::success($this->service->analytics($filters));
    }
}
