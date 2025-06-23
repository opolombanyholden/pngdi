<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Constructeur - Vérifier que l'utilisateur est un opérateur
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user() && auth()->user()->role !== 'operator') {
                abort(403, 'Accès non autorisé');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        return view('operator.dashboard');
    }

    public function index()
    {
        return view('operator.profil.index');
    }

    public function edit()
    {
        return view('operator.profil.edit');
    }

    public function update(Request $request)
    {
        // Logique de mise à jour
        return redirect()->route('operator.profil.index')
            ->with('success', 'Profil mis à jour avec succès');
    }

    public function updatePassword(Request $request)
    {
        // Logique de mise à jour du mot de passe
        return redirect()->route('operator.profil.index')
            ->with('success', 'Mot de passe mis à jour avec succès');
    }

    public function guides()
    {
        return view('operator.guides');
    }

    public function documentsTypes()
    {
        return view('operator.documents-types');
    }

    public function calendrier()
    {
        return view('operator.calendrier');
    }
}