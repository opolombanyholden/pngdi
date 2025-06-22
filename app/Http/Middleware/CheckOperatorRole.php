<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOperatorRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== 'operator') {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}