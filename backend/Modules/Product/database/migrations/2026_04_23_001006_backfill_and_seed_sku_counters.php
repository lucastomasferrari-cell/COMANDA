<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Mapping entity_type => tabla. Este es el manifest de la feature:
     * las 4 entidades tienen la misma forma (columna sku nullable +
     * sku_locked boolean) post-migrations 001000..001004.
     *
     * Orden del backfill importa poco porque los contadores son
     * independientes, pero seguimos el orden alfabético por consistencia
     * con otros sweeps.
     */
    private const ENTITIES = [
        'categories' => 'categories',
        'menus' => 'menus',
        'options' => 'options',
        'products' => 'products',
    ];

    public function up(): void
    {
        $logPath = storage_path('logs/sku-backfill-' . now()->toDateString() . '.log');
        $this->log($logPath, '=== SKU backfill start: ' . now()->toDateTimeString() . ' ===');

        foreach (self::ENTITIES as $entityType => $table) {
            $this->log($logPath, "--- Processing $entityType ($table) ---");

            // Paso 1 — resolver duplicates cross-row (puede existir en
            // products si el vendor permitia (sku, menu_id) compuesto con
            // distintos menu_ids compartiendo sku. Los otros 3 son
            // recién creados, no tienen data previa).
            $this->resolveDuplicates($table, $logPath);

            // Paso 2 — seed counter al max numérico + 1 (o 1 si vacío).
            // Los SKUs no-numéricos (ej "ROLL-001") se ignoran en el max,
            // siguen intactos y no colisionan con los numéricos futuros.
            $maxNumeric = DB::table($table)
                ->whereRaw("sku REGEXP '^[0-9]+$'")
                ->when(Schema::hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->max(DB::raw('CAST(sku AS UNSIGNED)'));

            $start = ((int)($maxNumeric ?? 0)) + 1;

            DB::table('sku_counters')->upsert(
                [[
                    'entity_type' => $entityType,
                    'next_value' => $start,
                    'updated_at' => now(),
                ]],
                ['entity_type'],
                ['next_value', 'updated_at']
            );

            $this->log($logPath, "counter '$entityType' seeded: next_value=$start (max_numeric=" . ($maxNumeric ?? 'none') . ')');

            // Paso 3 — backfill de NULL / vacío ordenados por created_at ASC.
            $rows = DB::table($table)
                ->where(fn($q) => $q->whereNull('sku')->orWhere('sku', ''))
                ->when(Schema::hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            $next = $start;
            foreach ($rows as $id) {
                DB::table($table)->where('id', $id)->update(['sku' => (string)$next]);
                $this->log($logPath, "  $entityType:$id => $next");
                $next++;
            }

            DB::table('sku_counters')
                ->where('entity_type', $entityType)
                ->update(['next_value' => $next, 'updated_at' => now()]);

            $assigned = $next - $start;
            $this->log($logPath, "$entityType: $assigned rows backfilled, counter advanced to $next");
        }

        // Paso 4 — agregar unique global a las 4 tablas DESPUÉS del backfill
        // y la resolución de duplicates, para que no choque.
        foreach (self::ENTITIES as $entityType => $table) {
            Schema::table($table, function (Blueprint $t) use ($entityType) {
                $t->unique('sku', "{$entityType}_sku_global_unique");
            });
            $this->log($logPath, "unique global added on $table(sku) as {$entityType}_sku_global_unique");
        }

        $this->log($logPath, '=== SKU backfill done: ' . now()->toDateTimeString() . ' ===');
    }

    public function down(): void
    {
        // Dropeamos los uniques y el counter. NO revertimos los SKUs
        // backfillados — son datos reales. Si la feature se revierte por
        // completo, las migrations de columnas (001000..001004) limpian
        // todo al bajarse.
        foreach (self::ENTITIES as $entityType => $table) {
            Schema::table($table, function (Blueprint $t) use ($entityType) {
                $t->dropUnique("{$entityType}_sku_global_unique");
            });
        }

        DB::table('sku_counters')->whereIn('entity_type', array_keys(self::ENTITIES))->delete();
    }

    /**
     * Por cada SKU duplicado, conservamos la fila con menor id (más
     * antigua por creación normalmente) y nulificamos las demás. Los
     * NULLs después reciben valor en el paso 3 del backfill.
     */
    private function resolveDuplicates(string $table, string $logPath): void
    {
        $dupGroups = DB::table($table)
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->when(Schema::hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->groupBy('sku')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('sku');

        foreach ($dupGroups as $sku) {
            $ids = DB::table($table)
                ->where('sku', $sku)
                ->when(Schema::hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            $keep = array_shift($ids);
            foreach ($ids as $id) {
                DB::table($table)->where('id', $id)->update(['sku' => null]);
                $this->log($logPath, "  duplicate resolved: $table id=$id had sku='$sku', nulled (kept id=$keep)");
            }
        }
    }

    private function log(string $path, string $message): void
    {
        file_put_contents($path, '[' . now()->toDateTimeString() . "] $message\n", FILE_APPEND);
    }
};
