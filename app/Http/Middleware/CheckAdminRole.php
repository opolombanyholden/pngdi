<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur a le rôle admin ou agent
        if (!in_array($user->role, ['admin', 'agent'])) {
            // Redirection selon le rôle de l'utilisateur
            switch ($user->role) {
                case 'operator':
                    return redirect()->route('operator.dashboard')
                        ->with('error', 'Accès refusé. Cette section est réservée aux administrateurs et agents.');
                default:
                    return redirect()->route('home')
                        ->with('error', 'Accès refusé. Vous n\'avez pas les privilèges nécessaires.');
            }
        }

        // Vérifications supplémentaires pour les agents/admins
        if (in_array($user->role, ['admin', 'agent'])) {
            // Vérifier que le compte est actif (si le champ existe)
            if (isset($user->statut) && $user->statut !== 'actif') {
                Auth::logout();
                return redirect()->route('home')
                    ->with('error', 'Votre compte a été désactivé. Contactez l\'administration.');
            }

            // Vérifier l'email vérifié
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('warning', 'Veuillez vérifier votre adresse email avant d\'accéder à l\'administration.');
            }
        }

        return $next($request);
    }
}