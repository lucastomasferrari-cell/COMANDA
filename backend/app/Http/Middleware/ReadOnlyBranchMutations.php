<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * En modo 1-instalacion-1-sucursal, crear o borrar branches no tiene
 * sentido. Se permite PUT (editar datos del local: nombre, direccion,
 * CUIT, payment methods, etc.) y GET (leer). El bloqueo vive a nivel
 * middleware para no tocar las rutas del vendor (Modules/Branch/routes/).
 */
class ReadOnlyBranchMutations
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->isBranchesMutation($request)) {
            throw new MethodNotAllowedHttpException(
                ['GET', 'PUT'],
                'Branches are immutable in single-branch installation mode.'
            );
        }

        return $next($request);
    }

    private function isBranchesMutation(Request $request): bool
    {
        return preg_match('#^api/v\d+/branches(/|$)#', $request->path()) === 1
            && in_array($request->method(), ['POST', 'DELETE'], true);
    }
}
