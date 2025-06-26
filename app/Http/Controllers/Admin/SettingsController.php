<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Page principale des paramètres
     */
    public function index()
    {
        return view('admin.settings.index', [
            'user' => auth()->user(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env'),
                'maintenance_mode' => app()->isDownForMaintenance()
            ],
            'user_preferences' => $this->getUserPreferences()
        ]);
    }

    /**
     * Préférences utilisateur par défaut
     */
    private function getUserPreferences()
    {
        return [
            'timezone' => 'Africa/Libreville',
            'language' => 'fr',
            'theme' => 'light',
            'notifications_email' => true,
            'sidebar_collapsed' => false
        ];
    }
}
