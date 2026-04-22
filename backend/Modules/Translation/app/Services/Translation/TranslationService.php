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
            'lang_files' => $this->getLangFilesFingerprint(),
        ]));
    }

    /**
     * Fingerprint de los archivos de traduccion del sistema, para que el
     * versioning detecte cambios en .php files (no solo rows en la tabla
     * translations). Sin esto, agregar keys nuevas a un lang/*.php no
     * invalidaba el cache de IndexedDB del frontend -> seguian apareciendo
     * keys crudas hasta limpiar el browser a mano.
     *
     * Cubre: backend/lang/<locale>/*.php (Laravel root) +
     * backend/Modules/<M>/lang/<locale>/*.php (vendor modulos).
     * Si mas adelante agregamos subdirectorios tipo lang/<locale>/emails/,
     * hay que ampliar los patrones (hoy solo profundidad 2).
     */
    private function getLangFilesFingerprint(): string
    {
        $patterns = [
            base_path('lang/*/*.php'),
            base_path('Modules/*/lang/*/*.php'),
        ];

        $mtimes = [];
        foreach ($patterns as $pattern) {
            $matches = glob($pattern) ?: [];
            foreach ($matches as $file) {
                $mtimes[$file] = @filemtime($file) ?: 0;
            }
        }

        ksort($mtimes);
        return md5(serialize($mtimes));
    }

    /** @implements */
    public function retrieve(): array
    {
        return Translation::retrieve();
    }
}
