<?php

namespace Modules\AuditLog\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\AuditLog\Models\AuditLog;

/**
 * Archiva y borra audit logs > 1 año donde is_fiscal = false.
 *
 *   php artisan audit:cleanup              --> ejecuta
 *   php artisan audit:cleanup --dry-run    --> solo lista
 *   php artisan audit:cleanup --days=365   --> override default
 *
 * Los registros con is_fiscal = true NUNCA se borran (retención
 * AFIP 10 años — otro job cubre eso eventualmente).
 *
 * Archivo: storage/app/audit-archive/YYYY-MM-DD.jsonl.gz
 * Formato: 1 registro JSON por línea, gzip compress.
 */
class CleanupAuditLogs extends Command
{
    protected $signature = 'audit:cleanup
        {--days=365 : Edad mínima en días para archivar y borrar}
        {--dry-run : Solo reportar qué haría, sin escribir ni borrar}
        {--chunk=1000 : Tamaño de chunk para streaming}';

    protected $description = 'Archiva y elimina audit logs antiguos no fiscales.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $cutoff = Carbon::now()->subDays($days);

        $query = AuditLog::query()
            ->where('is_fiscal', false)
            ->where('created_at', '<', $cutoff);

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info("No hay audit logs > {$days} días sin flag fiscal. Nada que hacer.");
            return self::SUCCESS;
        }

        $this->info("Encontrados {$total} registros a archivar (cutoff: {$cutoff->toDateTimeString()}).");

        if ($dryRun) {
            $sample = (clone $query)->orderBy('id')->limit(5)->get(['id', 'action', 'user_id', 'created_at']);
            $this->table(
                ['id', 'action', 'user_id', 'created_at'],
                $sample->map(fn($r) => [$r->id, $r->action, $r->user_id, (string) $r->created_at])->toArray(),
            );
            $this->warn('Dry-run: no se archivó ni borró nada.');
            return self::SUCCESS;
        }

        $archiveFile = 'audit-archive/' . Carbon::now()->toDateString() . '.jsonl.gz';
        $disk = Storage::disk('local');
        $tempPath = tempnam(sys_get_temp_dir(), 'audit-') . '.jsonl.gz';
        $handle = gzopen($tempPath, 'wb9');

        if ($handle === false) {
            $this->error("No se pudo abrir archivo temporal {$tempPath}.");
            return self::FAILURE;
        }

        $archivedCount = 0;
        $deletedCount = 0;

        try {
            (clone $query)->orderBy('id')->chunkById($chunkSize, function ($rows) use ($handle, &$archivedCount) {
                foreach ($rows as $row) {
                    gzwrite($handle, json_encode($row->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
                    $archivedCount++;
                }
            });

            gzclose($handle);

            // Subir a disk configurado (local o s3 en prod).
            $disk->put($archiveFile, file_get_contents($tempPath));
            @unlink($tempPath);

            // Delete en chunks. DB::delete evita tocar el Model (que
            // tira LogicException en delete()).
            $deletedCount = DB::table('audit_logs')
                ->where('is_fiscal', false)
                ->where('created_at', '<', $cutoff)
                ->delete();

            $this->info("Archivados: {$archivedCount} registros → {$archiveFile}");
            $this->info("Eliminados de DB: {$deletedCount} registros.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if (is_resource($handle)) {
                gzclose($handle);
            }
            @unlink($tempPath);
            $this->error("Falló cleanup: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
