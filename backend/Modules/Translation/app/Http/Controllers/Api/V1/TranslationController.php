<?php

namespace Modules\Translation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;
use Modules\Translation\Http\Requests\Api\V1\UpdateTranslationRequest;
use Modules\Translation\Services\Translation\TranslationServiceInterface;

class TranslationController extends Controller
{
    /**
     * Create a new instance of TranslationController
     *
     * @param TranslationServiceInterface $service
     */
    public function __construct(protected TranslationServiceInterface $service)
    {
    }

    /**
     * Display a listing of the translations.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success(["records" => $this->service->retrieve()]);
    }

    /**
     * Update the specified translation in storage.
     *
     * @param UpdateTranslationRequest $request
     * @param string $key
     * @return JsonResponse
     */
    public function update(UpdateTranslationRequest $request, string $key): JsonResponse
    {
        $this->service->update($key, $request->get('locale'), $request->value);

        return ApiResponse::success(null, __('admin::messages.resource_updated', ['resource' => $this->service->label()]));
    }

    /**
     * Get application translations
     *
     * @return JsonResponse
     */
    public function getAppTranslations(): JsonResponse
    {
        return ApiResponse::success($this->service->getAppTranslations());
    }
}
