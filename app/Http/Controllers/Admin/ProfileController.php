<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Afficher le profil admin
     */
    public function index()
    {
        $user = auth()->user();
        
        return view('admin.profile.index', [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'account_info' => [
                'created_at' => $user->created_at->format('d/m/Y'),
                'last_login' => $user->updated_at->format('d/m/Y H:i'),
                'total_actions' => 0
            ]
        ]);
    }

    /**
     * Statistiques utilisateur
     */
    private function getUserStats($user)
    {
        return [
            'dossiers_traites' => 0,
            'actions_today' => 0,
            'login_count' => 0,
            'account_age' => $user->created_at->diffForHumans()
        ];
    }
}
