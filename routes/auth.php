<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Routes d'Authentification PNGDI - Version Corrigée
|--------------------------------------------------------------------------
| Ces routes utilisent les contrôleurs existants du projet
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    
    // === INSCRIPTION ===
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
        ->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // === CONNEXION ===
    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // === MOT DE PASSE OUBLIÉ (Routes basiques Laravel) ===
    Route::get('forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::post('forgot-password', function () {
        return redirect()->back()->with('status', 'Fonctionnalité en cours de développement');
    })->name('password.email');

    // === RÉINITIALISATION MOT DE PASSE (Routes basiques Laravel) ===
    Route::get('reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
    
    Route::post('reset-password', function () {
        return redirect()->route('login')->with('status', 'Fonctionnalité en cours de développement');
    })->name('password.store');

    // === AUTHENTIFICATION À DEUX FACTEURS (2FA) ===
    Route::get('two-factor', [TwoFactorController::class, 'index'])
        ->name('two-factor.index');
    Route::post('two-factor', [TwoFactorController::class, 'verify'])
        ->name('two-factor.verify');
    Route::post('two-factor/resend', [TwoFactorController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('two-factor.resend');
});

Route::middleware('auth')->group(function () {
    
    // === VÉRIFICATION EMAIL ===
    Route::get('email/verify', [VerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    Route::get('email/verified', [VerificationController::class, 'verified'])
        ->name('email.verified');

    // === CONFIRMATION MOT DE PASSE (Routes basiques) ===
    Route::get('confirm-password', function () {
        return view('auth.confirm-password');
    })->name('password.confirm');
    
    Route::post('confirm-password', function () {
        // Logique basique de confirmation
        return redirect()->intended();
    });

    // === GESTION 2FA (pour utilisateurs connectés) ===
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('setup', function () {
            return view('auth.two-factor-setup');
        })->name('setup');
        
        Route::post('enable', function () {
            $user = auth()->user();
            $user->update(['two_factor_enabled' => true]);
            return redirect()->back()->with('success', '2FA activé avec succès');
        })->name('enable');
        
        Route::post('disable', function () {
            $user = auth()->user();
            $user->update(['two_factor_enabled' => false]);
            return redirect()->back()->with('success', '2FA désactivé avec succès');
        })->middleware('password.confirm')->name('disable');
        
        Route::post('recovery-codes', function () {
            return response()->json(['message' => 'Fonctionnalité en cours de développement']);
        })->name('recovery-codes');
    });

    // === DÉCONNEXION ===
    Route::post('logout', [LoginController::class, 'logout'])
        ->name('logout');
});

/*
|--------------------------------------------------------------------------
| Routes de récupération d'urgence (admin uniquement)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('emergency')->name('emergency.')->group(function () {
    Route::post('disable-2fa/{user}', function ($userId) {
        $user = \App\Models\User::findOrFail($userId);
        $user->update([
            'two_factor_enabled' => false,
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
        
        return redirect()->back()->with('success', 'Compte utilisateur débloqué et 2FA désactivé');
    })->name('disable-2fa');
    
    Route::post('unlock-account/{user}', function ($userId) {
        $user = \App\Models\User::findOrFail($userId);
        $user->resetFailedAttempts();
        
        return redirect()->back()->with('success', 'Compte utilisateur débloqué');
    })->name('unlock-account');
});

/*
|--------------------------------------------------------------------------
| Routes API pour authentification
|--------------------------------------------------------------------------
*/
Route::prefix('api/auth')->name('api.auth.')->middleware(['throttle:30,1'])->group(function () {
    // Vérifier si un email existe
    Route::post('check-email', function (Illuminate\Http\Request $request) {
        $exists = \App\Models\User::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists]);
    })->name('check-email');
    
    // Vérifier la force du mot de passe
    Route::post('check-password-strength', function (Illuminate\Http\Request $request) {
        $password = $request->password;
        $score = 0;
        
        if (strlen($password) >= 8) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;
        
        $strength = 'Très faible';
        switch($score) {
            case 0:
            case 1:
                $strength = 'Très faible';
                break;
            case 2:
                $strength = 'Faible';
                break;
            case 3:
                $strength = 'Moyen';
                break;
            case 4:
                $strength = 'Fort';
                break;
            case 5:
                $strength = 'Très fort';
                break;
        }
        
        return response()->json([
            'score' => $score,
            'strength' => $strength
        ]);
    })->name('check-password-strength');
    
    // Statut de la session
    Route::middleware('auth')->get('session-status', function () {
        return response()->json([
            'authenticated' => true,
            'user' => auth()->user()->only(['name', 'email', 'role']),
            'expires_at' => session()->get('expires_at', now()->addMinutes(config('session.lifetime')))
        ]);
    })->name('session-status');
});

/*
|--------------------------------------------------------------------------
| Routes de développement pour les tests d'authentification
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    // Test rapide 2FA
    Route::get('test-2fa', function () {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        return [
            'user' => $user->only(['name', 'email', 'role']),
            '2fa_enabled' => $user->two_factor_enabled ?? false,
            '2fa_required' => in_array($user->role, ['admin', 'agent']),
            'email_verified' => !is_null($user->email_verified_at),
            'can_access_dashboard' => $user->email_verified_at && 
                (!in_array($user->role, ['admin', 'agent']) || ($user->two_factor_enabled ?? false))
        ];
    })->name('test-2fa');
    
    // Forcer la vérification d'email
    Route::get('force-verify-email/{user}', function ($userId) {
        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->markEmailAsVerified();
            return "Email vérifié pour {$user->email}";
        }
        return "Utilisateur non trouvé";
    })->name('force-verify-email');
}