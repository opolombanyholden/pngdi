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
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\DossierController as AdminDossierController;
use App\Http\Controllers\Admin\ReferentielController;
use App\Http\Controllers\Admin\ContentController;

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
| Routes d'authentification
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
| Routes Admin - Dashboard Principal et APIs
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // ========================================
    // APIs TEMPS RÉEL pour Dashboard
    // ========================================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Statistiques principales
        Route::get('/stats', [DashboardController::class, 'getStatsApi'])->name('stats');
        
        // Feed d'activité récente
        Route::get('/activity', [DashboardController::class, 'getActivityFeed'])->name('activity');
        
        // Données pour graphiques
        Route::get('/chart-data', [DashboardController::class, 'getChartDataApi'])->name('chart-data');
        
        // APIs supplémentaires pour fonctionnalités avancées
        Route::get('/agents-status', [DashboardController::class, 'getAgentsStatus'])->name('agents-status');
        Route::get('/priority-dossiers', [DashboardController::class, 'getPriorityDossiersApi'])->name('priority-dossiers');
        Route::get('/performance-metrics', [DashboardController::class, 'getPerformanceMetricsApi'])->name('performance-metrics');
        
        // APIs pour interface admin
        Route::get('/search/all', function() {
            return response()->json(['results' => [], 'message' => 'Recherche globale - Étape 6 à venir']);
        })->name('search.all');
        
        Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
        
    });
    
    // ========================================
    // WORKFLOW - Gestion des Dossiers (ÉTAPE 7)
    // ========================================
    /*
    Route::prefix('workflow')->name('workflow.')->group(function () {
        
        // En Attente - PREMIÈRE ACTIVATION
        Route::get('/en-attente', [WorkflowController::class, 'enAttente'])->name('en-attente');
        
        // En Cours - DEUXIÈME ACTIVATION  
        Route::get('/en-cours', [WorkflowController::class, 'enCours'])->name('en-cours');
        
        // Terminés - TROISIÈME ACTIVATION
        Route::get('/termines', [WorkflowController::class, 'termines'])->name('termines');
        
        // Actions sur les dossiers
        Route::post('/assign/{dossier}', [WorkflowController::class, 'assign'])->name('assign');
        Route::post('/validate/{validation}', [WorkflowController::class, 'validate'])->name('validate');
        Route::post('/reject/{validation}', [WorkflowController::class, 'reject'])->name('reject');
        
    });*/
    
    // ========================================
    // GESTION DES ENTITÉS - Routes complètes
    // ========================================
    Route::resource('organisations', \App\Http\Controllers\Admin\OrganisationController::class)->except(['create', 'store']);
    Route::resource('dossiers', AdminDossierController::class)->except(['create', 'store']);
    Route::resource('users', UserManagementController::class);
    Route::resource('declarations', \App\Http\Controllers\Admin\DeclarationController::class)->except(['create', 'store']);
    
    // Routes détaillées pour dossiers (compatibilité existant)
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/pending', [AdminDossierController::class, 'pending'])->name('pending');
        Route::get('/in-progress', [AdminDossierController::class, 'inProgress'])->name('in-progress');
        Route::get('/completed', [AdminDossierController::class, 'completed'])->name('completed');
        Route::get('/approved', [AdminDossierController::class, 'approved'])->name('approved');
        Route::get('/rejected', [AdminDossierController::class, 'rejected'])->name('rejected');
        Route::get('/{id}/assign', [AdminDossierController::class, 'showAssign'])->name('assign');
        Route::post('/{id}/approve', [AdminDossierController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AdminDossierController::class, 'reject'])->name('reject');
    });
    
    // Routes détaillées pour utilisateurs (compatibilité existant)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/operators', [UserManagementController::class, 'operators'])->name('operators');
        Route::get('/agents', [UserManagementController::class, 'agents'])->name('agents');
        Route::get('/admins', [UserManagementController::class, 'admins'])->name('admins');
    });
    
    // ========================================
    // ANALYTICS ET RAPPORTS
    // ========================================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/organisations', [AnalyticsController::class, 'organisations'])->name('organisations');
        Route::get('/agents', [AnalyticsController::class, 'agents'])->name('agents');
        Route::get('/declarations', [AnalyticsController::class, 'declarations'])->name('declarations');
        Route::get('/export/{type}', [AnalyticsController::class, 'export'])->name('export');
    });
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'reports'])->name('index');
        Route::get('/generate', [AnalyticsController::class, 'generateReport'])->name('generate');
        Route::get('/statistics', [AnalyticsController::class, 'statistics'])->name('statistics');
        Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
        Route::get('/export', [AnalyticsController::class, 'exportData'])->name('export');
    });
    
    // ========================================
    // GESTION DES RÉFÉRENTIELS
    // ========================================
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::resource('document-types', \App\Http\Controllers\Admin\DocumentTypeController::class);
        Route::resource('workflow-steps', \App\Http\Controllers\Admin\WorkflowStepController::class);
        Route::resource('validation-entities', \App\Http\Controllers\Admin\ValidationEntityController::class);
    });
    
    // Configuration système (ancien /config/)
    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        Route::get('/organizations', [ReferentielController::class, 'organizations'])->name('organizations');
        Route::get('/documents', [ReferentielController::class, 'documents'])->name('documents');
        Route::get('/locations', [ReferentielController::class, 'locations'])->name('locations');
        Route::get('/workflows', [ReferentielController::class, 'workflows'])->name('workflows');
        Route::get('/types', [ReferentielController::class, 'types'])->name('types');
        Route::get('/permissions', [ReferentielController::class, 'permissions'])->name('permissions');
        Route::get('/templates', [ReferentielController::class, 'templates'])->name('templates');
    });
    
    // ========================================
    // NOTIFICATIONS ET MESSAGES
    // ========================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
    
    // ========================================
    // PARAMÈTRES ET SYSTÈME
    // ========================================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
        Route::post('/maintenance', [SettingsController::class, 'toggleMaintenance'])->name('maintenance');
        Route::get('/system', [SettingsController::class, 'system'])->name('system');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
    });
    
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/settings', [SettingsController::class, 'systemSettings'])->name('settings');
        Route::get('/logs', [SettingsController::class, 'logs'])->name('logs');
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
    });
    
    // ========================================
    // PROFIL ADMINISTRATEUR
    // ========================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'index'])->name('index');
        Route::post('/update', [AdminProfileController::class, 'update'])->name('update');
        Route::post('/change-password', [AdminProfileController::class, 'changePassword'])->name('change-password');
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
    Route::prefix('organisations')->name('organisations.')->middleware(['check.organisation.limit'])->group(function () {
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
        Route::get('/', [AdherentController::class, 'indexGlobal'])->name('index');
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
    
    // Messagerie
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\MessageController::class, 'index'])->name('index');
        Route::get('/nouveau', [\App\Http\Controllers\Operator\MessageController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Operator\MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [\App\Http\Controllers\Operator\MessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [\App\Http\Controllers\Operator\MessageController::class, 'reply'])->name('reply');
        Route::post('/{message}/mark-read', [\App\Http\Controllers\Operator\MessageController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{message}', [\App\Http\Controllers\Operator\MessageController::class, 'destroy'])->name('destroy');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\MessageController::class, 'notifications'])->name('index');
        Route::post('/mark-all-read', [\App\Http\Controllers\Operator\MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/count', [\App\Http\Controllers\Operator\MessageController::class, 'unreadCount'])->name('count');
    });
    
    // Déclarations
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
| Routes API Publiques et Intégrations
|--------------------------------------------------------------------------
*/

// API publique (V1)
Route::prefix('api/v1')->name('api.v1.')->middleware(['api', 'throttle:60,1'])->group(function () {
    
    // Statistiques publiques (si autorisées)
    Route::get('/public-stats', [\App\Http\Controllers\Api\ApiController::class, 'publicStats'])->name('public-stats');
    
    // Vérification statut organisation (pour citoyens)
    Route::get('/organisation-status/{numero}', [\App\Http\Controllers\Api\ApiController::class, 'organisationStatus'])->name('organisation-status');
    
});

// Webhooks et intégrations
Route::prefix('webhooks')->name('webhooks.')->middleware(['webhook.signature'])->group(function () {
    
    // Intégration avec systèmes externes
    Route::post('/ministry-update', [\App\Http\Controllers\Api\WebhookController::class, 'ministryUpdate'])->name('ministry-update');
    Route::post('/payment-notification', [\App\Http\Controllers\Api\WebhookController::class, 'paymentNotification'])->name('payment-notification');
    
});

/*
|--------------------------------------------------------------------------
| Routes API pour vérifications temps réel
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('api.')->middleware(['auth'])->group(function () {
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
            'locks_actifs' => 0, // À adapter selon votre modèle
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
                'check.organisation.limit' => class_exists(\App\Http\Middleware\CheckOrganisationLimit::class),
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
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'phone' => '+24101234567',
                'city' => 'Libreville',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'agent@pngdi.ga'],
            [
                'name' => 'Agent PNGDI',
                'password' => bcrypt('agent123'),
                'role' => 'agent',
                'phone' => '+24101234568',
                'city' => 'Libreville',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'operator@pngdi.ga'],
            [
                'name' => 'Jean NGUEMA',
                'password' => bcrypt('operator123'),
                'role' => 'operator',
                'phone' => '+24101234569',
                'city' => 'Port-Gentil',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        return 'Utilisateurs de test créés !<br>' .
               '<strong>Admin :</strong> admin@pngdi.ga / admin123<br>' .
               '<strong>Agent :</strong> agent@pngdi.ga / agent123<br>' .
               '<strong>Opérateur :</strong> operator@pngdi.ga / operator123<br>' .
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