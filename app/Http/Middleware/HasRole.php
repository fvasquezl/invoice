<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        if (! $guard->user()->hasRole('super_admin')) {
            abort(403, 'No tienes permisos para acceder al panel de administración.');
        }

        return $next($request);
    }
}
