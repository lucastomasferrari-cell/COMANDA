<?php

namespace Modules\User\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\AuditLog\Services\AuditLogger;
use Modules\User\Models\User;

/**
 * Verificacion del PIN de manager.
 *
 * - Intentos fallidos incrementan users.manager_pin_failed_attempts.
 * - A los 3 fallos, lockout_until se setea 30s adelante y el contador
 *   resetea a 0.
 * - Verify con lockout activo devuelve 429 via service (Controller lo
 *   traduce).
 * - Al exito, genera un token UUID en cache con TTL 5 min. El token
 *   se consume UNA sola vez (consumeToken lo elimina al leerlo).
 * - Todo intento (ok o fallido) se loguea en audit_logs.
 */
class ManagerPinService
{
    private const LOCKOUT_SECONDS = 30;
    private const LOCKOUT_THRESHOLD = 3;
    private const TOKEN_TTL_SECONDS = 300; // 5 min
    private const CACHE_PREFIX = 'manager_pin:';

    public function setPin(User $user, string $pin): void
    {
        abort_unless(preg_match('/^\d{4,6}$/', $pin), 422, __('user::messages.manager_pin_format_invalid'));
        $user->update([
            'manager_pin_hash' => Hash::make($pin),
            'manager_pin_failed_attempts' => 0,
            'manager_pin_lockout_until' => null,
        ]);
    }

    /**
     * Devuelve el token al verificar OK, o null si falla.
     * Aborta 423 si hay lockout activo.
     */
    public function verify(int $userId, string $pin, ?string $actionContext = null): ?string
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);
        abort_unless($user && $user->manager_pin_hash, 404, __('user::messages.manager_pin_not_set'));

        // Lockout activo?
        if ($user->manager_pin_lockout_until && $user->manager_pin_lockout_until > now()) {
            AuditLogger::log('manager_pin_locked', $user, [
                'reason' => 'PIN lockout aún activo — intento bloqueado.',
                'metadata' => [
                    'action_context' => $actionContext,
                    'lockout_until' => $user->manager_pin_lockout_until->toIso8601String(),
                ],
            ]);
            abort(423, __('user::messages.manager_pin_locked_out'));
        }

        if (!Hash::check($pin, $user->manager_pin_hash)) {
            $attempts = $user->manager_pin_failed_attempts + 1;
            $updates = ['manager_pin_failed_attempts' => $attempts];
            if ($attempts >= self::LOCKOUT_THRESHOLD) {
                $updates['manager_pin_lockout_until'] = now()->addSeconds(self::LOCKOUT_SECONDS);
                $updates['manager_pin_failed_attempts'] = 0;
            }
            $user->update($updates);

            AuditLogger::log('manager_pin_failed', $user, [
                'reason' => 'PIN incorrecto',
                'metadata' => [
                    'action_context' => $actionContext,
                    'attempts' => $attempts,
                    'will_lockout' => $attempts >= self::LOCKOUT_THRESHOLD,
                ],
            ]);

            return null;
        }

        // Success — reset contador, emitir token
        $user->update([
            'manager_pin_failed_attempts' => 0,
            'manager_pin_lockout_until' => null,
        ]);

        $token = (string) Str::uuid();
        Cache::put(self::CACHE_PREFIX . $token, [
            'user_id' => $user->id,
            'action_context' => $actionContext,
            'created_at' => now()->toIso8601String(),
        ], self::TOKEN_TTL_SECONDS);

        AuditLogger::log('manager_pin_verified', $user, [
            'reason' => $actionContext,
            'metadata' => [
                'action_context' => $actionContext,
                'token_ttl_seconds' => self::TOKEN_TTL_SECONDS,
            ],
        ]);

        return $token;
    }

    /**
     * Consume un token. Devuelve el payload si es válido; null si
     * no existe / ya se usó / expiró.
     */
    public function consumeToken(string $token): ?array
    {
        $key = self::CACHE_PREFIX . $token;
        $payload = Cache::get($key);
        if (!$payload) {
            return null;
        }
        Cache::forget($key);
        return $payload;
    }
}
