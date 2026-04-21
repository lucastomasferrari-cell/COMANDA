<?php

namespace Modules\Translation\Services\Translation;

use App\Forkiva;
use Modules\Translation\Models\Translation;

class TranslationService implements TranslationServiceInterface
{
    /** @implements */
    public function label(): string
    {
        return __("translation::translations.translation");
    }

    /** @implements */
    public function update(string $key, string $locale, string $value): bool
    {
        return Translation::query()
            ->firstOrNew(['key' => $key])
            ->setTranslations('value', [$locale => $value])
            ->save();
    }

    public function getAppTranslations(): array
    {
        return Translation::appRetrieve();
    }

    /** @implements */
    public function getAppTranslationsVersion(): string
    {
        $snapshot = Translation::query()
            ->selectRaw('COUNT(*) as total_translations, MAX(updated_at) as latest_updated_at')
            ->first();

        return sha1(json_encode([
            'total' => (int)($snapshot?->total_translations ?? 0),
            'updated_at' => $snapshot?->latest_updated_at,
            'app_version' => Forkiva::$version,
            'locale' => locale(),
        ]));
    }

    /** @implements */
    public function retrieve(): array
    {
        return Translation::retrieve();
    }
}
