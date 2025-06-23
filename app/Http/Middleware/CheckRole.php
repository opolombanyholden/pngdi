<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Vérifier si le rôle de l'utilisateur est dans la liste des rôles autorisés
        if (!in_array($userRole, $roles)) {
            abort(403, 'Accès non autorisé. Rôles autorisés : ' . implode(', ', $roles));
        }

        return $next($request);
    }
}