<?php

namespace Modules\POS\Tests\Unit;

use Tests\TestCase;

/**
 * Sprint 3.A.bis bug 1 — test preventivo.
 *
 * Recorre todos los .vue del modulo POS frontend, extrae las keys
 * pos::pos_viewer.* invocadas con $t() o t(), y verifica que cada
 * key exista en el archivo lang es_AR/pos_viewer.php.
 *
 * Protege contra el patron recurrente del sprint (3 incidentes:
 * Sprint 0 tabs, Sprint 2 order_types, Sprint 3.A modes) donde se
 * agregan keys al template sin agregarlas al lang.
 *
 * Solo chequea es_AR porque es el locale default del install. En/Ar
 * quedan como "soft miss" — el fallback es_AR cubre el render
 * (vue-i18n fallbackLocale).
 */
class TranslationKeysAreDefinedTest extends TestCase
{
    private string $frontendSrcDir;

    private string $langFile;

    protected function setUp(): void
    {
        parent::setUp();
        // Scanneamos TODO frontend/src, no solo modules/pos — las keys
        // pos::pos_viewer.* se usan tambien desde app/layouts (PosHeader)
        // y modules/seatingPlan. El regex filtra por namespace, asi que
        // scan ancho + match angosto funciona bien.
        $this->frontendSrcDir = realpath(__DIR__.'/../../../../../frontend/src');
        $this->langFile = realpath(__DIR__.'/../../lang/es_AR/pos_viewer.php');
    }

    public function test_every_pos_viewer_key_used_in_vue_templates_exists_in_es_ar_lang(): void
    {
        $this->assertNotFalse($this->frontendSrcDir, 'Frontend src dir not found');
        $this->assertNotFalse($this->langFile, 'es_AR/pos_viewer.php not found');

        $translations = require $this->langFile;
        $vueFiles = $this->findVueFiles($this->frontendSrcDir);
        $this->assertGreaterThan(0, count($vueFiles), 'No .vue files found in frontend');

        $missing = [];
        foreach ($vueFiles as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }
            $keys = $this->extractPosViewerKeys($content);
            foreach ($keys as $key) {
                if (! $this->keyExistsInArray($translations, $key)) {
                    $missing[] = sprintf('%s → %s', $this->relativePath($file), $key);
                }
            }
        }

        $this->assertEmpty(
            $missing,
            "Missing pos::pos_viewer.* keys in es_AR/pos_viewer.php:\n".implode("\n", array_unique($missing))
        );
    }

    private function findVueFiles(string $dir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'vue') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Extrae todas las keys invocadas como $t('pos::pos_viewer.foo.bar')
     * o t('pos::pos_viewer.foo.bar'). Ignora concatenaciones / templates
     * dinamicos (t(`pos::pos_viewer.${x}`)) — no son chequeables
     * estaticamente.
     */
    private function extractPosViewerKeys(string $content): array
    {
        $pattern = "/\\bt\\(\\s*['\"]pos::pos_viewer\\.([a-zA-Z0-9_.]+)['\"]/";
        preg_match_all($pattern, $content, $matches);

        return array_unique($matches[1] ?? []);
    }

    private function keyExistsInArray(array $translations, string $dottedKey): bool
    {
        $segments = explode('.', $dottedKey);
        $cursor = $translations;
        foreach ($segments as $seg) {
            if (! is_array($cursor) || ! array_key_exists($seg, $cursor)) {
                return false;
            }
            $cursor = $cursor[$seg];
        }

        // Aceptamos tanto strings (hoja) como arrays (key que referencia
        // un grupo, ej: "active_orders" que contiene title/subtitle).
        return true;
    }

    private function relativePath(string $absolute): string
    {
        $needle = 'frontend/src/';
        $idx = strpos(str_replace('\\', '/', $absolute), $needle);

        return $idx === false ? basename($absolute) : substr(str_replace('\\', '/', $absolute), $idx);
    }
}
