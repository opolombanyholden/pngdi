<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicControllers\HomeController;
use App\Http\Controllers\PublicControllers\ActualiteController;
use App\Http\Controllers\PublicControllers\DocumentController;
use App\Http\Controllers\PublicControllers\AnnuaireController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Routes Web Publiques
|--------------------------------------------------------------------------
| Routes accessibles à tous les visiteurs
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Pages d'information
Route::get('/a-propos', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/guides', [HomeController::class, 'guides'])->name('guides');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');

// Actualités
Route::prefix('actualites')->name('actualites.')->group(function () {
    Route::get('/', [ActualiteController::class, 'index'])->name('index');
    Route::get('/{slug}', [ActualiteController::class, 'show'])->name('show');
});

// Documents et ressources
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/download/{id}', [DocumentController::class, 'download'])->name('download');
});

// Annuaire des organisations
Route::prefix('annuaire')->name('annuaire.')->group(function () {
    Route::get('/', [AnnuaireController::class, 'index'])->name('index');
    Route::get('/associations', [AnnuaireController::class, 'associations'])->name('associations');
    Route::get('/ong', [AnnuaireController::class, 'ong'])->name('ong');
    Route::get('/partis-politiques', [AnnuaireController::class, 'partisPolitiques'])->name('partis');
    Route::get('/confessions-religieuses', [AnnuaireController::class, 'confessionsReligieuses'])->name('confessions');
    Route::get('/{type}/{slug}', [AnnuaireController::class, 'show'])->name('show');
});

// Calendrier des événements
Route::get('/calendrier', [HomeController::class, 'calendrier'])->name('calendrier');

/*
|--------------------------------------------------------------------------
| Routes d'authentification
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Inscription
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Connexion
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Mot de passe oublié (temporaire)
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    // Routes 2FA
    Route::get('/two-factor', [TwoFactorController::class, 'index'])->name('two-factor.index');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});

// Déconnexion
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Routes de vérification d'email
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Page de notice de vérification
    Route::get('/email/verify', [VerificationController::class, 'notice'])
        ->name('verification.notice');

    // Vérification via le lien
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Renvoyer l'email de vérification
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Page de confirmation après vérification
    Route::get('/email/verified', [VerificationController::class, 'verified'])
        ->name('email.verified');
});

/*
|--------------------------------------------------------------------------
| Routes protégées (authentification requise)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'account.status'])->group(function () {
    // Redirection selon le rôle après connexion
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if (in_array($user->role, ['admin', 'agent'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'operator') {
            return redirect()->route('operator.dashboard');
        }
        
        return redirect()->route('home');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'account.status'])->group(function () {
    Route::get('/', function () {
        // Vérification du rôle dans la route elle-même
        if (!in_array(auth()->user()->role, ['admin', 'agent'])) {
            abort(403, 'Accès non autorisé');
        }
        return view('admin.dashboard');
    })->name('dashboard');
    
    // Routes admin à implémenter
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', function () {
            if (!in_array(auth()->user()->role, ['admin', 'agent'])) {
                abort(403, 'Accès non autorisé');
            }
            return 'Liste des dossiers - À implémenter';
        })->name('index');
    });
    
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function () {
            if (!in_array(auth()->user()->role, ['admin', 'agent'])) {
                abort(403, 'Accès non autorisé');
            }
            return 'Gestion des utilisateurs - À implémenter';
        })->name('index');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Operator
|--------------------------------------------------------------------------
*/

Route::prefix('operator')->name('operator.')->middleware(['auth', 'verified', 'account.status'])->group(function () {
    Route::get('/', function () {
        // Vérification du rôle dans la route elle-même
        if (auth()->user()->role !== 'operator') {
            abort(403, 'Accès non autorisé');
        }
        return view('operator.dashboard');
    })->name('dashboard');
    
    // Routes operator à implémenter
    Route::prefix('organisations')->name('organisations.')->group(function () {
        Route::get('/', function () {
            if (auth()->user()->role !== 'operator') {
                abort(403, 'Accès non autorisé');
            }
            return 'Mes organisations - À implémenter';
        })->name('index');
        
        Route::get('/create', function () {
            if (auth()->user()->role !== 'operator') {
                abort(403, 'Accès non autorisé');
            }
            return 'Créer une organisation - À implémenter';
        })->name('create');
    });
    
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', function () {
            if (auth()->user()->role !== 'operator') {
                abort(403, 'Accès non autorisé');
            }
            return 'Mes dossiers - À implémenter';
        })->name('index');
    });
});

/*
|--------------------------------------------------------------------------
| Route de test pour vérifier l'installation
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return [
        'laravel_version' => app()->version(),
        'php_version' => PHP_VERSION,
        'environment' => config('app.env'),
        'debug_mode' => config('app.debug'),
        'app_url' => config('app.url'),
        'timezone' => config('app.timezone'),
        'locale' => config('app.locale'),
        'database_connected' => DB::connection()->getPdo() ? 'Yes' : 'No',
        'users_count' => \App\Models\User::count(),
        'current_user' => auth()->check() ? auth()->user()->email : 'Non connecté',
    ];
})->name('test');

/*
|--------------------------------------------------------------------------
| Routes de développement (à supprimer en production)
|--------------------------------------------------------------------------
*/

if (config('app.debug')) {
    // Route pour créer un utilisateur de test
    Route::get('/create-test-users', function () {
        // Admin avec 2FA activé
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@pngdi.ga'],
            [
                'name' => 'Admin PNGDI',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'phone' => '+24101234567',
                'city' => 'Libreville',
                'is_active' => true,
                'email_verified_at' => now(),
                'two_factor_enabled' => true, // 2FA activé
            ]
        );

        // Agent avec 2FA activé
        \App\Models\User::firstOrCreate(
            ['email' => 'agent@pngdi.ga'],
            [
                'name' => 'Agent PNGDI',
                'password' => bcrypt('password123'),
                'role' => 'agent',
                'phone' => '+24101234568',
                'city' => 'Libreville',
                'is_active' => true,
                'email_verified_at' => now(),
                'two_factor_enabled' => true, // 2FA activé
            ]
        );

        // Operator sans 2FA
        \App\Models\User::firstOrCreate(
            ['email' => 'operator@pngdi.ga'],
            [
                'name' => 'Jean NGUEMA',
                'password' => bcrypt('password123'),
                'role' => 'operator',
                'phone' => '+24101234569',
                'city' => 'Port-Gentil',
                'is_active' => true,
                'email_verified_at' => now(),
                'two_factor_enabled' => false, // Pas de 2FA pour les opérateurs
            ]
        );

        return 'Utilisateurs de test créés avec succès !<br><br>' .
               '<strong>Admin (avec 2FA) :</strong> admin@pngdi.ga / password123<br>' .
               '<strong>Agent (avec 2FA) :</strong> agent@pngdi.ga / password123<br>' .
               '<strong>Opérateur (sans 2FA) :</strong> operator@pngdi.ga / password123<br><br>' .
               '<a href="/login" class="btn btn-primary">Aller à la connexion</a>';
    })->name('create-test-users');
    
    // Route pour simuler la vérification d'email
    Route::get('/verify-email-test/{id}', function ($id) {
        $user = \App\Models\User::find($id);
        if ($user) {
            $user->markEmailAsVerified();
            return redirect()->route('login')->with('success', 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.');
        }
        return 'Utilisateur non trouvé';
    })->name('verify-email-test');
}