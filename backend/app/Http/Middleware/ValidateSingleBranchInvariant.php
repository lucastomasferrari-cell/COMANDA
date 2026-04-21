<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Defense-in-depth para la estrategia 1-instalacion-1-sucursal. Corre en
 * cada request HTTP y valida que branches.count() == 1. Si alguien metio
 * una branch extra por SQL directo (tampering, bug, dump erroneo), aborta
 * con 503 y loguea critical para alerting.
 *
 * No corre en CLI: queremos que artisan (migrate/seed/fresh install) no
 * quede bloqueado si la DB esta temporalmente sin branches. Los endpoints
 * /up (health) e /installer/* (setup wizard) se saltan explicitamente.
 *
 * Cache de 60s: la query es barata pero igual evitamos pegarle a MySQL en
 * cada request. Latencia maxima de deteccion de tampering: 60s.
 */
class ValidateSingleBranchInvariant
{
    private const CACHE_KEY = 'single_branch.invariant_check';
    private const CACHE_TTL = 60;
    private const EXPECTED_COUNT = 1;

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        try {
            $count = Cache::remember(
                self::CACHE_KEY,
                self::CACHE_TTL,
                fn () => DB::table('branches')->count()
            );
        } catch (Throwable $e) {
            // DB inaccesible o tabla branches aun no creada: no enmascarar
            // con un 503 generico. Los endpoints individuales fallaran con
            // su propio error y el mensaje sera mas util para debugging.
            Log::warning('Single-branch invariant check could not run', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            return $next($request);
        }

        if ($count !== self::EXPECTED_COUNT) {
            Log::critical('Single-branch invariant violated', [
                'branches_count' => $count,
                'expected' => self::EXPECTED_COUNT,
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
            abort(503, "System state invalid: expected exactly 1 branch, found {$count}.");
        }

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        $path = $request->path();
        return $path === 'up' || str_starts_with($path, 'installer');
    }
}
