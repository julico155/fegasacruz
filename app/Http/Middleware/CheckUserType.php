<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        // Verifica si el usuario está autenticado
        if (!Auth::check()) {
            // Si no está autenticado, redirige a la página de login o aborta con 403
            return redirect()->route('login'); // O abort(403, 'No autenticado.');
        }

        // Obtiene el usuario autenticado
        $user = Auth::user();

        // Verifica si el tipo de usuario está en la lista de tipos permitidos
        // Por ejemplo, si se llama con 'check.user.type:administrativo', $types contendrá ['administrativo']
        if (!in_array($user->tipo, $types)) {
            // Si el tipo de usuario no está permitido, aborta con un error 403 (Acceso Denegado)
            abort(403, 'Acceso no autorizado. Tu tipo de usuario no tiene permisos para acceder a esta sección.');
        }

        // Si el usuario está autenticado y su tipo es permitido, permite que la solicitud continúe
        return $next($request);
    }
}
