<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function statistiques()
    {
        return view('admin.statistiques.index');
    }

    public function export()
    {
        // Logique d'export
        return response()->json(['message' => 'Export en cours...']);
    }

    public function generateRapport(Request $request)
    {
        // Logique de génération de rapport
        return response()->json(['message' => 'Rapport généré']);
    }

    public function logs()
    {
        return view('admin.logs.index');
    }

    public function exportLogs()
    {
        // Logique d'export des logs
        return response()->json(['message' => 'Export des logs en cours...']);
    }
}