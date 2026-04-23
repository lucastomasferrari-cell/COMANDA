<?php

namespace Modules\AuditLog\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\AuditLog\Http\Controllers\Api\V1\AntiFraudDashboardController;
use Modules\AuditLog\Mail\DailyAntifraudReport as DailyAntifraudReportMail;

/**
 * Genera el reporte del día anterior y lo envia al owner_alert_email
 * configurado en settings. Si PASE está conectado (futuro), el envio
 * se delega a ese bridge — por ahora mail directo.
 */
class DailyAntifraudReport extends Command
{
    protected $signature = 'reports:daily-antifraud {--date=}';
    protected $description = 'Envía el reporte anti-fraude del día anterior al dueño.';

    public function handle(): int
    {
        if (!(bool) setting('antifraud.daily_report_enabled', true)) {
            $this->info('Reporte diario anti-fraude deshabilitado (setting antifraud.daily_report_enabled=false).');
            return self::SUCCESS;
        }

        $email = setting('antifraud.owner_alert_email');
        if (empty($email)) {
            $this->warn('No hay owner_alert_email configurado. Skip.');
            return self::SUCCESS;
        }

        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        // Reusamos el controller para armar el payload — request fake con
        // from/to del día del reporte.
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'from' => $date->copy()->startOfDay()->toIso8601String(),
            'to' => $date->copy()->endOfDay()->toIso8601String(),
        ]);

        $controller = app(AntiFraudDashboardController::class);
        $response = $controller->summary($request);
        $summary = $response->getData(true)['body'] ?? [];
        $summary['date'] = $date->toDateString();

        Mail::to($email)->send(new DailyAntifraudReportMail($summary));

        $this->info("Reporte enviado a {$email} para {$date->toDateString()}.");
        return self::SUCCESS;
    }
}
