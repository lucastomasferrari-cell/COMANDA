<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

/**
 * Opt-in idempotency via header X-Idempotency-Key.
 *
 * Si el cliente manda la header en POST/PUT/PATCH/DELETE, la primera
 * request se procesa y la response se guarda en idempotency_keys. Replay
 * de la misma key devuelve la response cacheada con header
 * X-Idempotent-Replay: true, sin re-procesar el handler.
 *
 * Scope del match: key + scope (user:X | api_client:X | anonymous) +
 * method + path. Dos endpoints distintos con la misma key del cliente
 * no colisionan porque el path forma parte de la clave.
 *
 * Concurrencia: UNIQUE constraint (key, scope, method, path) + INSERT
 * atomico. Si dos requests concurrentes con misma key entran al mismo
 * tiempo, uno gana el INSERT y procesa; el otro lo ve con status=
 * processing y recibe 409 (cliente decide retry con backoff). Si el
 * status=processing quedo orphan (mas de 60s), se toma y continua.
 *
 * TTL 24h. Cleanup via scheduler daily (DELETE WHERE expires_at < NOW()).
 *
 * Response cached no incluye headers sensibles (auth, cookies, Set-Cookie).
 * Solo se preserva Content-Type.
 */
class IdempotencyKey
{
    private const HEADER_NAME = 'X-Idempotency-Key';
    private const REPLAY_HEADER = 'X-Idempotent-Replay';
    private const TTL_HOURS = 24;
    private const ORPHAN_THRESHOLD_SECONDS = 60;
    private const MUTATION_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (!$this->shouldApply($request)) {
            return $next($request);
        }

        $key = $request->header(self::HEADER_NAME);
        if (!$this->isValidKey($key)) {
            abort(400, 'Invalid X-Idempotency-Key: must be 16-128 alphanumeric/hyphen characters.');
        }

        $scope = $this->scopeFor($request);
        $method = $request->method();
        $path = $request->path();

        $existing = $this->findExisting($key, $scope, $method, $path);
        if ($existing !== null) {
            $decision = $this->decideFromExisting($existing);
            if ($decision === 'replay') {
                return $this->replayResponse($existing);
            }
            if ($decision === 'conflict') {
                abort(409, 'Request with this idempotency key is already being processed.');
            }
            // decision === 'take_orphan' cae en el INSERT/UPDATE siguiente.
            $this->resetOrphan($existing->id);
        } else {
            // Intento de INSERT atomico. Si choca con UNIQUE por race concurrente,
            // re-leer y volver al arbol de decisiones.
            if (!$this->tryInsertProcessing($key, $scope, $method, $path)) {
                $existing = $this->findExisting($key, $scope, $method, $path);
                if ($existing === null) {
                    // Caso improbable (row eliminado entre las dos queries).
                    abort(409, 'Idempotency key conflict; please retry.');
                }
                $decision = $this->decideFromExisting($existing);
                if ($decision === 'replay') {
                    return $this->replayResponse($existing);
                }
                abort(409, 'Request with this idempotency key is already being processed.');
            }
        }

        $response = $next($request);

        try {
            $this->persistResponse($key, $scope, $method, $path, $response);
        } catch (Throwable $e) {
            Log::warning('IdempotencyKey middleware failed to persist response', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'key' => $key,
                'scope' => $scope,
                'path' => $path,
            ]);
        }

        return $response;
    }

    private function shouldApply(Request $request): bool
    {
        return in_array($request->method(), self::MUTATION_METHODS, true)
            && $request->hasHeader(self::HEADER_NAME);
    }

    private function isValidKey(?string $key): bool
    {
        return is_string($key)
            && preg_match('/^[A-Za-z0-9\-_]{16,128}$/', $key) === 1;
    }

    private function scopeFor(Request $request): string
    {
        $user = $request->user();
        if ($user !== null) {
            return 'user:' . $user->getAuthIdentifier();
        }
        return 'anonymous';
    }

    private function findExisting(string $key, string $scope, string $method, string $path): ?object
    {
        $row = DB::table('idempotency_keys')
            ->where('key', $key)
            ->where('scope', $scope)
            ->where('method', $method)
            ->where('path', $path)
            ->first();
        return $row === null ? null : (object) $row;
    }

    private function decideFromExisting(object $row): string
    {
        if ($row->status === 'completed' || $row->status === 'failed') {
            return 'replay';
        }
        // status === 'processing'
        $updatedAt = Carbon::parse($row->updated_at ?? $row->created_at);
        if ($updatedAt->diffInSeconds(now()) >= self::ORPHAN_THRESHOLD_SECONDS) {
            return 'take_orphan';
        }
        return 'conflict';
    }

    private function tryInsertProcessing(string $key, string $scope, string $method, string $path): bool
    {
        try {
            DB::table('idempotency_keys')->insert([
                'key' => $key,
                'scope' => $scope,
                'method' => $method,
                'path' => $path,
                'status' => 'processing',
                'expires_at' => now()->addHours(self::TTL_HOURS),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function resetOrphan(int $id): void
    {
        DB::table('idempotency_keys')
            ->where('id', $id)
            ->update([
                'status' => 'processing',
                'response_status' => null,
                'response_headers' => null,
                'response_body' => null,
                'updated_at' => now(),
                'expires_at' => now()->addHours(self::TTL_HOURS),
            ]);
    }

    private function replayResponse(object $row): SymfonyResponse
    {
        $headers = $row->response_headers ? json_decode($row->response_headers, true) : [];
        $response = new Response(
            content: $row->response_body ?? '',
            status: $row->response_status ?? 200,
            headers: is_array($headers) ? $headers : [],
        );
        $response->headers->set(self::REPLAY_HEADER, 'true');
        return $response;
    }

    private function persistResponse(string $key, string $scope, string $method, string $path, SymfonyResponse $response): void
    {
        $status = $response->getStatusCode();
        $contentType = $response->headers->get('Content-Type');
        $body = $response->getContent();
        $finalStatus = $status >= 500 ? 'failed' : 'completed';

        DB::table('idempotency_keys')
            ->where('key', $key)
            ->where('scope', $scope)
            ->where('method', $method)
            ->where('path', $path)
            ->update([
                'status' => $finalStatus,
                'response_status' => $status,
                'response_headers' => $contentType ? json_encode(['Content-Type' => $contentType]) : null,
                'response_body' => is_string($body) ? $body : null,
                'updated_at' => now(),
            ]);
    }
}
