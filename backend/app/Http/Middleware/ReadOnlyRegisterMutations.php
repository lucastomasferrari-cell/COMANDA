<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Single-register mode: COMANDA MVP tiene UNA sola pos_register
 * ("CAJA"). Crear o borrar registers por API no tiene sentido —
 * el seed inicial ya instaló la única válida. Permitimos GET (leer
 * para el POS) y PUT (editar nombre, impresoras asignadas) pero
 * bloqueamos POST y DELETE.
 *
 * Trade-off: el admin podría querer renombrar "CAJA" a "CAJA Principal"
 * o similar. PUT lo permite. El endpoint DELETE queda bloqueado para
 * evitar que el dueño se dispare un pie — el POS depende de que exista
 * al menos una register activa.
 */
class ReadOnlyRegisterMutations
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->isRegisterMutation($request)) {
            throw new MethodNotAllowedHttpException(
                ['GET', 'PUT'],
                'Registers are immutable in single-register installation mode.'
            );
        }

        return $next($request);
    }

    private function isRegisterMutation(Request $request): bool
    {
        return preg_match('#^api/v\d+/pos/registers(/|$)#', $request->path()) === 1
            && in_array($request->method(), ['POST', 'DELETE'], true);
    }
}
