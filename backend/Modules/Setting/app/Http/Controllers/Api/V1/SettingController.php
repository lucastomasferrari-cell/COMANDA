<?php

namespace Modules\Setting\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\Controller;
use Modules\Setting\Enums\SettingSection;
use Modules\Setting\Http\Requests\Api\V1\SaveSettingRequest;
use Modules\Setting\Services\Setting\SettingServiceInterface;
use Modules\Support\ApiResponse;
use Modules\Translation\Services\Translation\TranslationServiceInterface;

class SettingController extends Controller
{
    /**
     * Create a new instance of SettingController
     *
     * @param SettingServiceInterface $service
     * @param TranslationServiceInterface $translationService
     */
    public function __construct(
        protected SettingServiceInterface $service,
        protected TranslationServiceInterface $translationService,
    )
    {
    }

    /**
     * Get settings and meta for a section
     *
     * @param SettingSection $section
     * @return JsonResponse
     */
    public function index(SettingSection $section): JsonResponse
    {
        return ApiResponse::success([
            "settings" => $this->service->getSettings($section),
            "meta" => $this->service->getMeta($section),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SaveSettingRequest $request
     * @param SettingSection $section
     * @return JsonResponse
     */
    public function update(SaveSettingRequest $request, SettingSection $section): JsonResponse
    {
        $this->service->update($section, $request->validated());

        return ApiResponse::success(
            ["app_settings" => $this->service->getAppSettings(true)],
            __(
                "admin::messages.resource_updated",
                [
                    'resource' => __("setting::settings.settings")
                ]
            )
        );
    }

    /**
     * This method retrieves and returns a list of Setting public models.
     *
     * @return JsonResponse
     */
    public function getAppSettings(): JsonResponse
    {
        return ApiResponse::success($this->service->getAppSettings());
    }

    /**
     * Get application boot meta.
     *
     * @return JsonResponse
     */
    public function getAppBootMeta(): JsonResponse
    {
        return ApiResponse::success([
            'settings_version' => $this->service->getAppSettingsVersion(),
            'translations_version' => $this->translationService->getAppTranslationsVersion(),
        ]);
    }
}
