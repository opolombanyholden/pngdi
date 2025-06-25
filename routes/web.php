<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicControllers\HomeController;
use App\Http\Controllers\PublicControllers\ActualiteController;
use App\Http\Controllers\PublicControllers\DocumentController;
use App\Http\Controllers\PublicControllers\AnnuaireController;
use App\Http\Controllers\Operator\ProfileController;
use App\Http\Controllers\Operator\DossierController;
use App\Http\Controllers\Operator\OrganisationController;
use App\Http\Controllers\Operator\AdherentController;
use App\Http\Controllers\Operator\DocumentController as OperatorDocumentController;

/*
|--------------------------------------------------------------------------
| Routes Web Publiques
|--------------------------------------------------------------------------
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
| Routes d'authentification - Délégation vers auth.php
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Routes protégées (authentification requise)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
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
| Routes Admin - Délégation vers admin.php
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    // Dashboard principal uniquement ici
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Operator
|--------------------------------------------------------------------------
*/
Route::prefix('operator')->name('operator.')->middleware(['auth', 'verified', 'operator'])->group(function () {
    
    // Dashboard principal
    Route::get('/', function () {
        return view('operator.dashboard');
    })->name('dashboard');
    
    // Profil opérateur
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::get('/complete', [ProfileController::class, 'complete'])->name('complete');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/stats', [ProfileController::class, 'getProfileStats'])->name('stats');
        Route::get('/export', [ProfileController::class, 'exportProfile'])->name('export');
        Route::delete('/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
    });
    
    // Organisations avec vérification des limites
    Route::prefix('organisations')->name('organisations.')->middleware(['organisation.limit'])->group(function () {
        Route::get('/', [OrganisationController::class, 'index'])->name('index');
        Route::get('/create', [OrganisationController::class, 'create'])->name('create');
        Route::post('/', [OrganisationController::class, 'store'])->name('store');
        Route::get('/{organisation}', [OrganisationController::class, 'show'])->name('show');
        Route::get('/{organisation}/edit', [OrganisationController::class, 'edit'])->name('edit');
        Route::put('/{organisation}', [OrganisationController::class, 'update'])->name('update');
        Route::delete('/{organisation}', [OrganisationController::class, 'destroy'])->name('destroy');
    });
    
    // Gestion des dossiers avec verrouillage
    Route::prefix('dossiers')->name('dossiers.')->middleware(['dossier.lock'])->group(function () {
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create/{type}', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        Route::post('/{dossier}/submit', [DossierController::class, 'submit'])->name('submit');
        Route::delete('/{dossier}', [DossierController::class, 'destroy'])->name('destroy');
        
        // Gestion des verrous (AJAX)
        Route::post('/{dossier}/extend-lock', [DossierController::class, 'extendLock'])->name('extend-lock');
        Route::post('/{dossier}/release-lock', [DossierController::class, 'releaseLock'])->name('release-lock');
    });
    
        // Gestion des adhérents
        Route::prefix('members')->name('members.')->group(function () {
        // Vue globale de tous les adhérents (corrigée)
        Route::get('/', [AdherentController::class, 'indexGlobal'])->name('index');
        
        // Adhérents par organisation (routes existantes)  
        Route::get('/organisation/{organisation}', [AdherentController::class, 'index'])->name('by-organisation');
        Route::get('/create', [AdherentController::class, 'create'])->name('create');
        Route::post('/', [AdherentController::class, 'store'])->name('store');
        Route::get('/{adherent}', [AdherentController::class, 'show'])->name('show');
        Route::get('/{adherent}/edit', [AdherentController::class, 'edit'])->name('edit');
        Route::put('/{adherent}', [AdherentController::class, 'update'])->name('update');
        Route::delete('/{adherent}', [AdherentController::class, 'destroy'])->name('destroy');
        
        // Import/Export
        Route::get('/import/template', [AdherentController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import', [AdherentController::class, 'import'])->name('import');
        Route::get('/export/{organisation}', [AdherentController::class, 'export'])->name('export');
        Route::post('/generate-link/{organisation}', [AdherentController::class, 'generateRegistrationLink'])->name('generate-link');
    });
    
    // Gestion des documents
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [OperatorDocumentController::class, 'index'])->name('index');
        Route::post('/upload', [OperatorDocumentController::class, 'upload'])->name('upload');
        Route::get('/{document}/download', [OperatorDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [OperatorDocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{document}/replace', [OperatorDocumentController::class, 'replace'])->name('replace');
    });
    
    // Rapports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('operator.reports.index');
        })->name('index');
        Route::get('/organisation', function () {
            return view('operator.reports.organisation');
        })->name('organisation');
        Route::get('/dossiers', function () {
            return view('operator.reports.dossiers');
        })->name('dossiers');
        Route::get('/adherents', function () {
            return view('operator.reports.adherents');
        })->name('adherents');
    });
    
    // Subventions
    Route::prefix('grants')->name('grants.')->group(function () {
        Route::get('/', function () {
            return view('operator.grants.index');
        })->name('index');
        Route::get('/demandes', function () {
            return view('operator.grants.demandes');
        })->name('demandes');
        Route::get('/historique', function () {
            return view('operator.grants.historique');
        })->name('historique');
    });
    
    // Aide
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', function () {
            return view('operator.help.index');
        })->name('index');
        Route::get('/guide', function () {
            return view('operator.help.guide');
        })->name('guide');
        Route::get('/faq', function () {
            return view('operator.help.faq');
        })->name('faq');
        Route::get('/contact', function () {
            return view('operator.help.contact');
        })->name('contact');
    });
    
    // Messagerie (routes principales pour le dashboard)
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\MessageController::class, 'index'])->name('index');
        Route::get('/nouveau', [\App\Http\Controllers\Operator\MessageController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Operator\MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [\App\Http\Controllers\Operator\MessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [\App\Http\Controllers\Operator\MessageController::class, 'reply'])->name('reply');
        Route::post('/{message}/mark-read', [\App\Http\Controllers\Operator\MessageController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{message}', [\App\Http\Controllers\Operator\MessageController::class, 'destroy'])->name('destroy');
    });
    
    // Notifications (routes principales pour le dashboard)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\MessageController::class, 'notifications'])->name('index');
        Route::post('/mark-all-read', [\App\Http\Controllers\Operator\MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/count', [\App\Http\Controllers\Operator\MessageController::class, 'unreadCount'])->name('count');
    });
    
    // Déclarations (routes principales pour le dashboard)
    Route::prefix('declarations')->name('declarations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\DeclarationController::class, 'index'])->name('index');
        Route::get('/create/{organisation}', [\App\Http\Controllers\Operator\DeclarationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Operator\DeclarationController::class, 'store'])->name('store');
        Route::get('/{declaration}', [\App\Http\Controllers\Operator\DeclarationController::class, 'show'])->name('show');
        Route::post('/{declaration}/soumettre', [\App\Http\Controllers\Operator\DeclarationController::class, 'soumettre'])->name('soumettre');
    });
});

/*
|--------------------------------------------------------------------------
| Routes API pour vérifications temps réel
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('api.')->middleware(['auth:sanctum'])->group(function () {
    // Vérification des limites d'organisation
    Route::get('/check-organisation-limit/{type}', function ($type) {
        $user = auth()->user();
        $count = $user->organisations()->where('type', $type)->where('statut', 'actif')->count();
        $limite = in_array($type, ['parti_politique', 'confession_religieuse']) ? 1 : null;
        
        return response()->json([
            'count' => $count,
            'limite' => $limite,
            'peut_creer' => $limite ? $count < $limite : true
        ]);
    })->name('check-organisation-limit');
    
    // Informations sur les verrous
    Route::get('/verrous/status', function () {
        return response()->json([
            'locks_actifs' => \App\Models\DossierValidation::where('locked_until', '>', now())->count(),
            'dernier_nettoyage' => cache('locks_last_cleanup', 'Jamais')
        ]);
    })->name('verrous.status');
});

/*
|--------------------------------------------------------------------------
| Routes de test (développement uniquement)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    Route::get('/test', function () {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'database_connected' => DB::connection()->getPdo() ? 'Yes' : 'No',
            'current_user' => auth()->check() ? auth()->user()->email : 'Non connecté',
            'middleware_loaded' => [
                'organisation.limit' => class_exists(\App\Http\Middleware\CheckOrganisationLimit::class),
                'dossier.lock' => class_exists(\App\Http\Middleware\DossierLock::class),
                'operator' => class_exists(\App\Http\Middleware\VerifyOperatorRole::class),
            ]
        ];
    })->name('test');
    
    // Créer des utilisateurs de test
    Route::get('/create-test-users', function () {
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
                'two_factor_enabled' => true,
            ]
        );

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
                'two_factor_enabled' => false,
            ]
        );

        return 'Utilisateurs de test créés !<br>' .
               '<strong>Admin :</strong> admin@pngdi.ga / password123<br>' .
               '<strong>Opérateur :</strong> operator@pngdi.ga / password123<br>' .
               '<a href="/login">Se connecter</a>';
    })->name('create-test-users');
}

// Inclure les routes admin supplémentaires
if (file_exists(__DIR__.'/admin.php')) {
    require __DIR__.'/admin.php';
}

// Inclure les routes operator supplémentaires  
if (file_exists(__DIR__.'/operator.php')) {
    require __DIR__.'/operator.php';
}

// Gestion des adhérents
    Route::prefix('members')->name('members.')->group(function () {
        // Vue globale de tous les adhérents (corrigée)
        Route::get('/', [AdherentController::class, 'indexGlobal'])->name('index');
        
        // Adhérents par organisation (routes existantes)  
        Route::get('/organisation/{organisation}', [AdherentController::class, 'index'])->name('by-organisation');
        Route::get('/create', [AdherentController::class, 'create'])->name('create');
        Route::post('/', [AdherentController::class, 'store'])->name('store');
        Route::get('/{adherent}', [AdherentController::class, 'show'])->name('show');
        Route::get('/{adherent}/edit', [AdherentController::class, 'edit'])->name('edit');
        Route::put('/{adherent}', [AdherentController::class, 'update'])->name('update');
        Route::delete('/{adherent}', [AdherentController::class, 'destroy'])->name('destroy');
        
        // Import/Export
        Route::get('/import/template', [AdherentController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import', [AdherentController::class, 'import'])->name('import');
        Route::get('/export/{organisation}', [AdherentController::class, 'export'])->name('export');
        Route::post('/generate-link/{organisation}', [AdherentController::class, 'generateRegistrationLink'])->name('generate-link');
    });