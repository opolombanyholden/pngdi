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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
| Routes de vérification QR Code (publiques)
|--------------------------------------------------------------------------
*/

Route::get('/verify/{type}/{code}', function($type, $code) {
    try {
        $qrService = new App\Services\QrCodeService();
        $result = $qrService->verifyCode($type, $code);
        
        if ($result['valid']) {
            return view('public.qr-verification-success', [
                'result' => $result,
                'type' => $type,
                'data' => $result['data']
            ]);
        } else {
            return view('public.qr-verification-error', [
                'result' => $result,
                'message' => $result['message']
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('Erreur vérification QR Code: ' . $e->getMessage());
        
        return view('public.qr-verification-error', [
            'result' => ['valid' => false],
            'message' => 'Erreur lors de la vérification du code'
        ]);
    }
})->name('public.verify');

// Route API pour vérification AJAX
Route::get('/api/verify/{type}/{code}', function($type, $code) {
    try {
        $qrService = new App\Services\QrCodeService();
        $result = $qrService->verifyCode($type, $code);
        
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'valid' => false,
            'message' => 'Erreur lors de la vérification'
        ], 500);
    }
})->name('api.verify');

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
| Routes Admin - VERSION TEST MINIMALE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // ========================================
    // DASHBOARD PRINCIPAL ET APIS - FONCTIONNEL
    // ========================================
    
    // Dashboard principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // APIs Temps Réel pour Dashboard
    Route::prefix('api')->name('api.')->group(function () {
        
        // Statistiques principales
        Route::get('/stats', [DashboardController::class, 'getStatsApi'])->name('stats');
        
        // Feed d'activité récente
        Route::get('/activity', [DashboardController::class, 'getActivityFeed'])->name('activity');
        
        // Données pour graphiques
        Route::get('/chart-data', [DashboardController::class, 'getChartDataApi'])->name('chart-data');
        
        // APIs supplémentaires
        Route::get('/agents-status', [DashboardController::class, 'getAgentsStatus'])->name('agents-status');
        Route::get('/priority-dossiers', [DashboardController::class, 'getPriorityDossiersApi'])->name('priority-dossiers');
        Route::get('/performance-metrics', [DashboardController::class, 'getPerformanceMetricsApi'])->name('performance-metrics');
        
        // APIs pour interface admin
        Route::get('/search/all', function() {
            return response()->json(['results' => [], 'message' => 'Recherche globale - Étape 6 à venir']);
        })->name('search.all');
        
        // Notifications existantes
        Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
        
    });
    
    // ========================================
    // ROUTES TEMPORAIRES (PLACEHOLDERS)
    // ========================================
    
    // Analytics existant
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    
    // Notifications existantes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
    
    // Profil admin existant
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    
    // Paramètres existants
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    
    // ========================================
    // WORKFLOW - ACTIVATION PROGRESSIVE (ÉTAPE 8)
    // ========================================
    
    Route::prefix('workflow')->name('workflow.')->group(function () {
        // En Attente - PREMIÈRE ACTIVATION ✅
        Route::get('/en-attente', [WorkflowController::class, 'enAttente'])->name('en-attente');
        
        // En Cours - DEUXIÈME ACTIVATION (prochaine étape)
        Route::get('/en-cours', [WorkflowController::class, 'enCours'])->name('en-cours');
        
        // Terminés - TROISIÈME ACTIVATION (prochaine étape)
        Route::get('/termines', [WorkflowController::class, 'termines'])->name('termines');
        
        // Actions sur les dossiers
        Route::post('/assign/{dossier}', [WorkflowController::class, 'assign'])->name('assign');
        Route::post('/validate/{validation}', [WorkflowController::class, 'validateDossier'])->name('validate');
        Route::post('/reject/{validation}', [WorkflowController::class, 'reject'])->name('reject');
    });
    
    // Gestion entités (temporaires)
    Route::get('/organisations', function() {
        return response()->json(['message' => 'Gestion organisations admin - Contrôleur à créer']);
    })->name('organisations.index');
    
    Route::get('/dossiers', function() {
        return response()->json(['message' => 'Gestion dossiers admin - Contrôleur à créer']);
    })->name('dossiers.index');
    
    Route::get('/users', function() {
        return response()->json(['message' => 'Gestion utilisateurs admin - Contrôleur à créer']);
    })->name('users.index');
    
    // Rapports (temporaires)
    Route::get('/reports', function() {
        return response()->json(['message' => 'Rapports admin - Contrôleur à créer']);
    })->name('reports.index');
    
    // Configuration (temporaires)
    Route::get('/config', function() {
        return response()->json(['message' => 'Configuration admin - Contrôleur à créer']);
    })->name('config.index');
    
    // Système (temporaires)
    Route::get('/system/settings', function() {
        return response()->json(['message' => 'Paramètres système - Contrôleur à créer']);
    })->name('system.settings');
    
    Route::get('/system/logs', function() {
        return response()->json(['message' => 'Logs système - Contrôleur à créer']);
    })->name('system.logs');
    
    Route::get('/system/backup', function() {
        return response()->json(['message' => 'Sauvegarde système - Contrôleur à créer']);
    })->name('system.backup');
    
});

/*
|--------------------------------------------------------------------------
| Routes Operator - CONSERVÉES INTÉGRALEMENT AVEC CORRECTIONS
|--------------------------------------------------------------------------
*/
Route::prefix('operator')->name('operator.')->middleware(['auth', 'verified', 'operator'])->group(function () {
    
    // Dashboard principal
    Route::get('/', function () {
        return view('operator.dashboard');
    })->name('dashboard');

     // ✅ AJOUTER CETTE LIGNE
    Route::get('/dashboard', function () {
        return view('operator.dashboard');
    })->name('dashboard.full');
    
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
    
    // ✅ ORGANISATIONS AVEC CORRECTIONS FINALES
    Route::prefix('organisations')->name('organisations.')->middleware(['check.organisation.limit'])->group(function () {
        Route::get('/', [OrganisationController::class, 'index'])->name('index');
        Route::get('/create', [OrganisationController::class, 'create'])->name('create');
        Route::post('/', [OrganisationController::class, 'store'])->name('store');
        Route::get('/{organisation}', [OrganisationController::class, 'show'])->name('show');
        Route::get('/{organisation}/edit', [OrganisationController::class, 'edit'])->name('edit');
        Route::put('/{organisation}', [OrganisationController::class, 'update'])->name('update');
        Route::delete('/{organisation}', [OrganisationController::class, 'destroy'])->name('destroy');
        
        // ✅ PAGE DE CONFIRMATION APRÈS CRÉATION
        Route::get('/confirmation/{dossier}', [OrganisationController::class, 'confirmation'])->name('confirmation');
        
        // ✅ TÉLÉCHARGEMENT ACCUSÉ DE RÉCEPTION
        Route::get('/download-accuse/{path}', [OrganisationController::class, 'downloadAccuse'])->name('download-accuse');
        
        // ✅ VÉRIFICATIONS AJAX EN TEMPS RÉEL
        Route::post('/check-existing-members', [OrganisationController::class, 'checkExistingMembers'])->name('check-existing-members');
        Route::post('/validate-organisation', [OrganisationController::class, 'validateOrganisation'])->name('validate');
        Route::post('/submit/{organisation}', [OrganisationController::class, 'submit'])->name('submit');
    });
    
    // ✅ GESTION DES DOSSIERS AVEC CORRECTIONS COMPLÈTES
    Route::prefix('dossiers')->name('dossiers.')->middleware(['dossier.lock'])->group(function () {
        
        // ✅ ROUTES SPÉCIFIQUES AVANT LES ROUTES DYNAMIQUES
        Route::get('/anomalies', [DossierController::class, 'anomalies'])->name('anomalies');
        Route::post('/anomalies/resolve/{adherent}', [DossierController::class, 'resolveAnomalie'])->name('anomalies.resolve');
        Route::get('/confirmation/{dossier}', [DossierController::class, 'confirmation'])->name('confirmation');
        
        // Routes existantes
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create/{type}', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');
        
        // ✅ ROUTES DYNAMIQUES À LA FIN
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
| Routes API pour Validation en Temps Réel - NOUVELLES ROUTES COMPLÈTES
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->name('api.')->middleware(['auth', 'throttle:60,1'])->group(function () {
    
    // ========================================
    // VÉRIFICATIONS EN TEMPS RÉEL (EXISTANTES)
    // ========================================
    
    /**
     * Vérification NIP gabonais
     * POST /api/v1/verify-nip
     */
    Route::post('/verify-nip', function (Request $request) {
        $request->validate([
            'nip' => 'required|string|size:13|regex:/^[0-9]{13}$/'
        ]);
        
        $nip = $request->input('nip');
        
        // Vérifier si le NIP existe déjà
        $exists = \App\Models\User::where('nip', $nip)->exists() ||
                 \App\Models\Adherent::where('nip', $nip)->exists() ||
                 \App\Models\Fondateur::where('nip', $nip)->exists();
        
        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Ce NIP est déjà utilisé' : 'NIP disponible'
        ]);
    })->name('verify-nip');
    
    /**
     * Vérification nom organisation
     * POST /api/v1/verify-organization-name
     */
    Route::post('/verify-organization-name', function (Request $request) {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'type' => 'required|in:association,ong,parti_politique,confession_religieuse',
            'suggest_alternatives' => 'boolean'
        ]);
        
        $name = $request->input('name');
        $type = $request->input('type');
        $suggestAlternatives = $request->input('suggest_alternatives', false);
        
        // Vérifier si le nom existe déjà
        $exists = \App\Models\Organisation::where('nom', $name)
                                        ->where('type', $type)
                                        ->exists();
        
        $response = [
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Ce nom est déjà utilisé pour ce type d\'organisation' : 'Nom disponible'
        ];
        
        // Générer des suggestions si demandé et si le nom existe
        if ($suggestAlternatives && $exists) {
            $suggestions = [];
            
            // Suggestions simples avec numéros
            for ($i = 1; $i <= 3; $i++) {
                $suggestion = $name . ' ' . $i;
                if (!\App\Models\Organisation::where('nom', $suggestion)->where('type', $type)->exists()) {
                    $suggestions[] = $suggestion;
                }
            }
            
            // Suggestions avec variantes
            $variants = ['Nouvelle', 'Grande', 'Moderne'];
            foreach ($variants as $variant) {
                $suggestion = $variant . ' ' . $name;
                if (!\App\Models\Organisation::where('nom', $suggestion)->where('type', $type)->exists()) {
                    $suggestions[] = $suggestion;
                    if (count($suggestions) >= 5) break;
                }
            }
            
            $response['suggestions'] = array_slice($suggestions, 0, 5);
        }
        
        return response()->json($response);
    })->name('verify-organization-name');
    
    /**
     * Vérification adhérents avec conflits parti
     * POST /api/v1/verify-members
     */
    Route::post('/verify-members', function (Request $request) {
        $request->validate([
            'members' => 'required|array',
            'members.*.nip' => 'required|string|size:13',
            'members.*.nom' => 'required|string',
            'members.*.prenom' => 'required|string',
            'organization_type' => 'required|string',
            'check_party_conflicts' => 'boolean'
        ]);
        
        $members = $request->input('members');
        $organizationType = $request->input('organization_type');
        $checkPartyConflicts = $request->input('check_party_conflicts', false);
        
        $conflicts = [];
        $duplicates = [];
        
        // Vérifier les doublons dans la liste soumise
        $nipCounts = array_count_values(array_column($members, 'nip'));
        foreach ($nipCounts as $nip => $count) {
            if ($count > 1) {
                $duplicates[] = $nip;
            }
        }
        
        // Vérifier les conflits avec les partis politiques existants
        if ($checkPartyConflicts && $organizationType === 'parti_politique') {
            foreach ($members as $member) {
                $existingMembership = \App\Models\Adherent::where('nip', $member['nip'])
                    ->whereHas('organisation', function ($query) {
                        $query->where('type', 'parti_politique')
                              ->where('statut', '!=', 'radie');
                    })
                    ->with('organisation')
                    ->first();
                
                if ($existingMembership) {
                    $conflicts[] = [
                        'nip' => $member['nip'],
                        'nom_complet' => $member['nom'] . ' ' . $member['prenom'],
                        'parti_actuel' => $existingMembership->organisation->nom,
                        'date_adhesion' => $existingMembership->date_adhesion->format('d/m/Y')
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'total_members' => count($members),
            'duplicates' => $duplicates,
            'conflicts' => $conflicts,
            'message' => count($conflicts) > 0 
                ? count($conflicts) . ' conflit(s) détecté(s)'
                : 'Aucun conflit détecté'
        ]);
    })->name('verify-members');
    
    // ========================================
    // GESTION DOCUMENTS (EXISTANTES)
    // ========================================
    
    /**
     * Upload de document
     * POST /api/v1/upload-document
     */
    Route::post('/upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'document_type' => 'required|string',
            'organization_id' => 'nullable|exists:organisations,id'
        ]);
        
        $file = $request->file('file');
        $documentType = $request->input('document_type');
        $organizationId = $request->input('organization_id');
        
        // Générer un nom unique
        $fileName = time() . '_' . $documentType . '.' . $file->getClientOriginalExtension();
        
        // Stocker le fichier
        $path = $file->storeAs('documents/temp', $fileName, 'public');
        
        return response()->json([
            'success' => true,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'message' => 'Document uploadé avec succès'
        ]);
    })->name('upload-document');
    
    /**
     * Preview de document
     * POST /api/v1/preview-document
     */
    Route::post('/preview-document', function (Request $request) {
        $request->validate([
            'file_path' => 'required|string',
            'type' => 'required|string'
        ]);
        
        $filePath = $request->input('file_path');
        $type = $request->input('type');
        
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        }
        
        $fullPath = Storage::disk('public')->path($filePath);
        $mimeType = mime_content_type($fullPath);
        
        $previewUrl = Storage::disk('public')->url($filePath);
        
        return response()->json([
            'success' => true,
            'preview_url' => $previewUrl,
            'file_type' => strpos($mimeType, 'pdf') !== false ? 'pdf' : 'image',
            'mime_type' => $mimeType
        ]);
    })->name('preview-document');
    
    // ========================================
    // SYSTÈME DE BROUILLONS (EXISTANTES)
    // ========================================
    
    /**
     * Sauvegarde brouillon
     * POST /api/v1/save-draft
     */
    Route::post('/save-draft', function (Request $request) {
        $request->validate([
            'form_data' => 'required|array',
            'step' => 'required|integer|min:1|max:9',
            'organization_type' => 'nullable|string'
        ]);
        
        $userId = auth()->id();
        $formData = $request->input('form_data');
        $step = $request->input('step');
        $organizationType = $request->input('organization_type');
        
        // Chercher un brouillon existant ou en créer un nouveau
        $draft = \App\Models\OrganizationDraft::updateOrCreate([
            'user_id' => $userId,
            'organization_type' => $organizationType
        ], [
            'form_data' => $formData,
            'current_step' => $step,
            'last_saved_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'message' => 'Brouillon sauvegardé avec succès'
        ]);
    })->name('save-draft');
    
    /**
     * Chargement brouillon
     * GET /api/v1/load-draft/{id}
     */
    Route::get('/load-draft/{id}', function ($id) {
        $userId = auth()->id();
        
        $draft = \App\Models\OrganizationDraft::where('id', $id)
                                             ->where('user_id', $userId)
                                             ->first();
        
        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'form_data' => $draft->form_data,
            'current_step' => $draft->current_step,
            'organization_type' => $draft->organization_type,
            'last_saved_at' => $draft->last_saved_at->toISOString()
        ]);
    })->name('load-draft');
    
    // ========================================
    // ANALYTICS ET SUIVI (EXISTANTES)
    // ========================================
    
    /**
     * Analytics de formulaire
     * POST /api/v1/form-analytics
     */
    Route::post('/form-analytics', function (Request $request) {
        $request->validate([
            'session_duration' => 'required|integer',
            'step_times' => 'required|array',
            'interactions' => 'required|array',
            'user_agent' => 'required|string',
            'screen_resolution' => 'required|string',
            'organization_type' => 'nullable|string',
            'completion_rate' => 'required|numeric|min:0|max:100'
        ]);
        
        // Enregistrer les analytics (vous pouvez créer un modèle FormAnalytics)
        $analytics = [
            'user_id' => auth()->id(),
            'session_duration' => $request->input('session_duration'),
            'step_times' => $request->input('step_times'),
            'interactions' => $request->input('interactions'),
            'user_agent' => $request->input('user_agent'),
            'screen_resolution' => $request->input('screen_resolution'),
            'organization_type' => $request->input('organization_type'),
            'completion_rate' => $request->input('completion_rate'),
            'created_at' => now()
        ];
        
        // Log pour l'instant (vous pouvez sauvegarder en DB plus tard)
        \Log::info('Form Analytics', $analytics);
        
        return response()->json([
            'success' => true,
            'message' => 'Analytics enregistrées'
        ]);
    })->name('form-analytics');
    
    /**
     * Validation complète avant soumission
     * POST /api/v1/validate-complete-form
     */
    Route::post('/validate-complete-form', function (Request $request) {
        $request->validate([
            'form_data' => 'required|array'
        ]);
        
        $formData = $request->input('form_data');
        $errors = [];
        
        // Validation basique - vous pouvez étendre selon vos besoins
        if (!isset($formData['metadata']['selectedOrgType']) || empty($formData['metadata']['selectedOrgType'])) {
            $errors[] = 'Type d\'organisation non sélectionné';
        }
        
        if (!isset($formData['steps'][3]['demandeur_nip']) || empty($formData['steps'][3]['demandeur_nip'])) {
            $errors[] = 'NIP du demandeur manquant';
        }
        
        if (!isset($formData['steps'][4]['org_nom']) || empty($formData['steps'][4]['org_nom'])) {
            $errors[] = 'Nom de l\'organisation manquant';
        }
        
        return response()->json([
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'message' => count($errors) === 0 
                ? 'Formulaire valide' 
                : 'Erreurs de validation détectées'
        ]);
    })->name('validate-complete-form');
});

/*
|--------------------------------------------------------------------------
| Routes API pour Gestion par Étapes - NOUVELLES ROUTES COMPLÈTES
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/organisation')->name('api.organisation.')->middleware(['auth', 'throttle:120,1'])->group(function () {

    // ========================================
    // GESTION DES ÉTAPES - NOUVELLES ROUTES
    // ========================================

    /**
     * Sauvegarder une étape spécifique
     * POST /api/v1/organisation/step/{step}/save
     */
    Route::post('/step/{step}/save', function (Request $request, $step) {
        $stepService = app(\App\Services\OrganisationStepService::class);

        $request->validate([
            'data' => 'required|array',
            'session_id' => 'nullable|string'
        ]);

        $result = $stepService->saveStep(
            (int) $step,
            $request->input('data'),
            auth()->id(),
            $request->input('session_id')
        );

        return response()->json($result);
    })->name('step.save');

    /**
     * Valider une étape sans sauvegarder
     * POST /api/v1/organisation/step/{step}/validate
     */
    Route::post('/step/{step}/validate', function (Request $request, $step) {
        $stepService = app(\App\Services\OrganisationStepService::class);

        $request->validate([
            'data' => 'required|array'
        ]);

        $result = $stepService->validateStep(
            (int) $step,
            $request->input('data')
        );

        return response()->json([
            'success' => $result['valid'],
            'valid' => $result['valid'],
            'errors' => $result['errors'],
            'step' => (int) $step
        ]);
    })->name('step.validate');

    /**
     * Récupérer les données d'une étape
     * GET /api/v1/organisation/draft/{draftId}/step/{step}
     */
    Route::get('/draft/{draftId}/step/{step}', function ($draftId, $step) {
        $stepService = app(\App\Services\OrganisationStepService::class);

        $result = $stepService->getStepData((int) $step, (int) $draftId);

        return response()->json($result);
    })->name('draft.step.get');

    /**
     * Vérifier si on peut accéder à une étape
     * GET /api/v1/organisation/draft/{draftId}/step/{step}/can-access
     */
    Route::get('/draft/{draftId}/step/{step}/can-access', function ($draftId, $step) {
        $stepService = app(\App\Services\OrganisationStepService::class);

        $canAccess = $stepService->canProceedToStep((int) $step, (int) $draftId);

        return response()->json([
            'success' => true,
            'can_access' => $canAccess,
            'step' => (int) $step,
            'draft_id' => (int) $draftId
        ]);
    })->name('draft.step.can-access');

    /**
     * Marquer une étape comme complétée
     * POST /api/v1/organisation/step/{step}/complete
     */
    Route::post('/step/{step}/complete', function (Request $request, $step) {
        $stepService = app(\App\Services\OrganisationStepService::class);

        $request->validate([
            'draft_id' => 'required|integer|exists:organization_drafts,id',
            'data' => 'required|array'
        ]);

        // Sauvegarder et marquer comme complétée
        $result = $stepService->saveStep(
            (int) $step,
            $request->input('data'),
            auth()->id()
        );

        if ($result['success']) {
            // Générer accusé si possible
            $draft = \App\Models\OrganizationDraft::find($request->input('draft_id'));

            if ($draft) {
                $stepService->generateStepAccuse((int) $step, $draft);
            }
        }

        return response()->json($result);
    })->name('step.complete');

    // ========================================
    // GESTION DES BROUILLONS
    // ========================================

    /**
     * Créer un nouveau brouillon
     * POST /api/v1/organisation/draft/create
     */
    Route::post('/draft/create', function (Request $request) {
        $request->validate([
            'organization_type' => 'nullable|in:association,ong,parti_politique,confession_religieuse',
            'session_id' => 'nullable|string'
        ]);

        $draft = \App\Models\OrganizationDraft::create([
            'user_id' => auth()->id(),
            'organization_type' => $request->input('organization_type'),
            'session_id' => $request->input('session_id'),
            'form_data' => [],
            'current_step' => 1,
            'completion_percentage' => 0,
            'last_saved_at' => now(),
            'expires_at' => now()->addDays(7)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brouillon créé avec succès',
            'draft' => $draft,
            'draft_id' => $draft->id
        ]);
    })->name('draft.create');

    /**
     * Récupérer un brouillon
     * GET /api/v1/organisation/draft/{draftId}
     */
    Route::get('/draft/{draftId}', function ($draftId) {
        $draft = \App\Models\OrganizationDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'draft' => $draft,
            'statistics' => $draft->getStatistics(),
            'steps_summary' => $draft->getStepsSummary(),
            'next_step' => $draft->getNextStep()
        ]);
    })->name('draft.get');

    /**
     * Lister les brouillons de l'utilisateur
     * GET /api/v1/organisation/drafts
     */
    Route::get('/drafts', function (Request $request) {
        $query = \App\Models\OrganizationDraft::where('user_id', auth()->id());

        // Filtres
        if ($request->has('type')) {
            $query->byType($request->input('type'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->boolean('recent_only')) {
            $query->recent();
        }

        $drafts = $query->orderBy('last_saved_at', 'desc')
            ->limit(20)
            ->get();

        $draftsWithStats = $drafts->map(function ($draft) {
            return [
                'id' => $draft->id,
                'organization_type' => $draft->organization_type,
                'current_step' => $draft->current_step,
                'completion_percentage' => $draft->completion_percentage,
                'last_saved_at' => $draft->last_saved_at,
                'expires_at' => $draft->expires_at,
                'is_expired' => $draft->isExpired(),
                'statistics' => $draft->getStatistics()
            ];
        });

        return response()->json([
            'success' => true,
            'drafts' => $draftsWithStats,
            'count' => $drafts->count()
        ]);
    })->name('drafts.list');

    /**
     * Supprimer un brouillon
     * DELETE /api/v1/organisation/draft/{draftId}
     */
    Route::delete('/draft/{draftId}', function ($draftId) {
        $draft = \App\Models\OrganizationDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ], 404);
        }

        $draft->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brouillon supprimé avec succès'
        ]);
    })->name('draft.delete');

    /**
     * Finaliser et créer l'organisation
     * POST /api/v1/organisation/draft/{draftId}/finalize
     */
    Route::post('/draft/{draftId}/finalize', function ($draftId) {
        $stepService = app(\App\Services\OrganisationStepService::class);

        $result = $stepService->finalizeOrganisation((int) $draftId);

        return response()->json($result);
    })->name('draft.finalize');

    // ========================================
    // UTILITAIRES ET STATISTIQUES
    // ========================================

    /**
     * Obtenir les statistiques globales de l'utilisateur
     * GET /api/v1/organisation/user-stats
     */
    Route::get('/user-stats', function () {
        $userId = auth()->id();

        $stats = [
            'total_drafts' => \App\Models\OrganizationDraft::where('user_id', $userId)->count(),
            'active_drafts' => \App\Models\OrganizationDraft::where('user_id', $userId)->active()->count(),
            'expired_drafts' => \App\Models\OrganizationDraft::where('user_id', $userId)->expired()->count(),
            'completed_organisations' => \App\Models\Organisation::where('user_id', $userId)->count(),
            'drafts_by_type' => \App\Models\OrganizationDraft::where('user_id', $userId)
                ->select('organization_type', \DB::raw('count(*) as count'))
                ->groupBy('organization_type')
                ->pluck('count', 'organization_type'),
            'average_completion' => \App\Models\OrganizationDraft::where('user_id', $userId)
                ->avg('completion_percentage') ?? 0
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    })->name('user.stats');

    /**
     * Nettoyer les brouillons expirés
     * POST /api/v1/organisation/cleanup-expired-drafts
     */
    Route::post('/cleanup-expired-drafts', function () {
        $deleted = \App\Models\OrganizationDraft::where('user_id', auth()->id())
            ->expired()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} brouillon(s) expiré(s) supprimé(s)",
            'deleted_count' => $deleted
        ]);
    })->name('cleanup.expired');

    /**
     * Étendre l'expiration d'un brouillon
     * POST /api/v1/organisation/draft/{draftId}/extend
     */
    Route::post('/draft/{draftId}/extend', function (Request $request, $draftId) {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:30'
        ]);

        $draft = \App\Models\OrganizationDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ], 404);
        }

        $days = $request->input('days', 7);
        $draft->extendExpiration($days);

        return response()->json([
            'success' => true,
            'message' => "Expiration étendue de {$days} jour(s)",
            'new_expiration' => $draft->expires_at
        ]);
    })->name('draft.extend');

});

/*
|--------------------------------------------------------------------------
| Routes pour accusés de réception par étapes
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/accuses')->name('api.accuses.')->middleware(['auth'])->group(function () {

    /**
     * Générer un accusé pour une étape
     * POST /api/v1/accuses/step/{step}/generate
     */
    Route::post('/step/{step}/generate', function (Request $request, $step) {
        $request->validate([
            'draft_id' => 'required|integer|exists:organization_drafts,id'
        ]);

        $draft = \App\Models\OrganizationDraft::find($request->input('draft_id'));

        // Vérifier l'ownership
        if ($draft->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $stepService = app(\App\Services\OrganisationStepService::class);
        $accuseGenerated = $stepService->generateStepAccuse((int) $step, $draft);

        return response()->json([
            'success' => $accuseGenerated,
            'message' => $accuseGenerated ?
                "Accusé généré pour l'étape {$step}" :
                "Impossible de générer l'accusé pour l'étape {$step}",
            'step' => (int) $step,
            'draft_id' => $draft->id
        ]);
    })->name('step.generate');

    /**
     * Lister les accusés d'un brouillon
     * GET /api/v1/accuses/draft/{draftId}
     */
    Route::get('/draft/{draftId}', function ($draftId) {
        $draft = \App\Models\OrganizationDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ], 404);
        }

        // Ici on listerait les accusés depuis la table draft_accuses
        // Pour l'instant, retourner un placeholder
        return response()->json([
            'success' => true,
            'accuses' => [],
            'count' => 0,
            'message' => 'Système d\'accusés en cours de développement'
        ]);
    })->name('draft.list');

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
    
    // Route de test pour la création d'organisation
    Route::post('/test-organisation-debug', function(Request $request) {
        return response()->json([
            'success' => true, 
            'message' => 'Route de test fonctionnelle',
            'data' => $request->all()
        ]);
    })->name('test-organisation-debug');
}

// Inclure les routes admin supplémentaires
if (file_exists(__DIR__.'/admin.php')) {
    require __DIR__.'/admin.php';
}

// Inclure les routes operator supplémentaires  
if (file_exists(__DIR__.'/operator.php')) {
    require __DIR__.'/operator.php';
}