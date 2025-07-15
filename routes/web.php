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
use App\Http\Controllers\Operator\ChunkingController;
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
| Routes Admin - VERSION TEST MINIMALE
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Routes Admin - VERSION MINIMALE SANS CONFLITS
|--------------------------------------------------------------------------
| CORRECTION COMPLÈTE : Suppression de tous les conflits avec admin.php
| Seules les APIs essentielles pour le dashboard sont conservées
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // APIs Temps Réel pour Dashboard (SANS CONFLIT)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStatsApi'])->name('stats');
        Route::get('/activity', [DashboardController::class, 'getActivityFeed'])->name('activity');
        Route::get('/chart-data', [DashboardController::class, 'getChartDataApi'])->name('chart-data');
        Route::get('/agents-status', [DashboardController::class, 'getAgentsStatus'])->name('agents-status');
        Route::get('/priority-dossiers', [DashboardController::class, 'getPriorityDossiersApi'])->name('priority-dossiers');
        Route::get('/performance-metrics', [DashboardController::class, 'getPerformanceMetricsApi'])->name('performance-metrics');
        Route::get('/search/all', function() {
            return response()->json(['results' => [], 'message' => 'Recherche globale - Étape 6 à venir']);
        })->name('search.all');
        Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    });
    
    // ⚠️ TOUTES LES SECTIONS CONFLICTUELLES SUPPRIMÉES :
    // - analytics (conflit avec admin.php)
    // - notifications (conflit avec admin.php)  
    // - settings (conflit avec admin.php)
    // - profile (différence structurelle)
    // - organisations, dossiers, users (conflits placeholders)
    
    // 🔗 REDIRECTION VERS admin.php POUR TOUTES LES AUTRES ROUTES
    // Les routes complètes sont gérées dans routes/admin.php
});

/*
|--------------------------------------------------------------------------
| Routes Operator - CORRIGÉES POUR CHUNKING
|--------------------------------------------------------------------------
*/
Route::prefix('operator')->name('operator.')->middleware(['web', 'auth', 'verified', 'operator'])->group(function () {
    
    // ========================================
    // ROUTES CHUNKING - INSERTION DURING CHUNKING (POSITION CORRIGÉE)
    // ========================================
    Route::prefix('chunking')->name('chunking.')->group(function () {
        
        Route::post('/process-chunk', [ChunkingController::class, 'processChunk'])
            ->name('process-chunk')
            ->middleware('throttle:30,1');
        
        Route::get('/csrf-refresh', [ChunkingController::class, 'refreshCSRF'])
            ->name('csrf-refresh');
        
        Route::get('/health', [ChunkingController::class, 'healthCheck'])
            ->name('health');
        
        Route::get('/auth-test', [ChunkingController::class, 'authTest'])
            ->name('auth-test');
    });

    // Templates et modèles
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/adherents-excel', [AdherentController::class, 'downloadTemplate'])->name('adherents-excel');
        Route::get('/adherents-csv', [AdherentController::class, 'downloadTemplate'])->name('adherents-csv');
    });

    // Dashboard principal
    Route::get('/', function () {
        return view('operator.dashboard');
    })->name('dashboard');
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
    
    // Organisations
    Route::prefix('organisations')->name('organisations.')->middleware(['check.organisation.limit'])->group(function () {
        Route::get('/', [OrganisationController::class, 'index'])->name('index');
        Route::get('/create', [OrganisationController::class, 'create'])->name('create');
        Route::post('/', [OrganisationController::class, 'store'])->name('store');
        Route::get('/{organisation}', [OrganisationController::class, 'show'])->name('show');
        Route::get('/{organisation}/edit', [OrganisationController::class, 'edit'])->name('edit');
        Route::put('/{organisation}', [OrganisationController::class, 'update'])->name('update');
        Route::delete('/{organisation}', [OrganisationController::class, 'destroy'])->name('destroy');
        
        // Workflow 2 phases
        Route::post('/store-phase1', [OrganisationController::class, 'storePhase1'])->name('store-phase1');
        Route::get('/download-accuse/{path}', [OrganisationController::class, 'downloadAccuse'])->name('download-accuse');
        
        // Vérifications AJAX
        Route::post('/check-existing-members', [OrganisationController::class, 'checkExistingMembers'])->name('check-existing-members');
        Route::post('/validate-organisation', [OrganisationController::class, 'validateOrganisation'])->name('validate');
        Route::post('/submit/{organisation}', [OrganisationController::class, 'submit'])->name('submit');

        // Session adhérents
        Route::post('/save-session-adherents', [OrganisationController::class, 'saveSessionAdherents'])->name('save-session-adherents');
        Route::post('/check-session-adherents', [OrganisationController::class, 'checkSessionAdherents'])->name('check-session-adherents');
        Route::post('/clear-session-adherents', [OrganisationController::class, 'clearSessionAdherents'])->name('clear-session-adherents');
        
        // Lots supplémentaires
        Route::post('/{dossier}/upload-additional-batch', [OrganisationController::class, 'uploadAdditionalBatch'])
            ->name('upload-additional-batch')
            ->middleware(['throttle:10,1']);
        Route::get('/{dossier}/adherents-statistics', [OrganisationController::class, 'getAdherentsStatisticsRealTime'])
            ->name('adherents-statistics')
            ->middleware(['throttle:60,1']);
        Route::post('/{dossier}/submit-to-administration', [OrganisationController::class, 'submitToAdministration'])
            ->name('submit-to-administration')
            ->middleware(['throttle:5,1']);
    });

        // Dans la section operator de web.php, ajoutez :
        Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\MessageController::class, 'notifications'])->name('index');
        Route::post('/mark-all-read', [\App\Http\Controllers\Operator\MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/count', [\App\Http\Controllers\Operator\MessageController::class, 'unreadCount'])->name('count');
    });
 
    // ========================================
    // GESTION DES DOSSIERS - CORRIGÉE
    // ========================================
    // Routes finalisation AJAX dans le groupe dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        
        // ✅ ROUTES FINALISATION AJAX (utilise colonnes existantes uniquement)
        Route::post('/{dossier}/finalize-later', [DossierController::class, 'finalizeLater'])
            ->name('finalize-later')
            ->middleware(['throttle:5,1'])
            ->where('dossier', '[0-9]+');
        
        Route::post('/{dossier}/finalize-now', [DossierController::class, 'finalizeNow'])
            ->name('finalize-now')
            ->middleware(['throttle:5,1'])
            ->where('dossier', '[0-9]+');
        
        // ✅ ROUTES SUPPORT FINALISATION (optionnelles)
        Route::get('/{dossier}/status-check', [DossierController::class, 'statusCheck'])
            ->name('status-check')
            ->middleware(['throttle:30,1'])
            ->where('dossier', '[0-9]+');

        // ✅ ROUTE CONFIRMATION
        Route::get('/{dossier}/confirmation', [DossierController::class, 'confirmation'])
            ->name('confirmation')
            ->where('dossier', '[0-9]+');
        
        // ✅ ROUTES PRINCIPALES DOSSIERS (existantes)
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create/{type}', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        Route::post('/{dossier}/submit', [DossierController::class, 'soumettre'])->name('submit');
        
        // ✅ ROUTES PHASE 2 - IMPORT ADHÉRENTS
        Route::get('/{dossier}/adherents-import', [DossierController::class, 'adherentsImportPage'])
            ->name('adherents-import')
            ->where('dossier', '[0-9]+');
            
        Route::post('/{dossier}/store-adherents', [DossierController::class, 'storeAdherentsPhase2'])
            ->name('store-adherents')
            ->where('dossier', '[0-9]+');
        
        // ✅ ROUTES DOCUMENTS
        Route::post('/{dossier}/upload-document', [DossierController::class, 'uploadDocument'])
            ->name('upload-document');
        Route::delete('/{dossier}/documents/{document}', [DossierController::class, 'deleteDocument'])
            ->name('delete-document');
        Route::get('/documents/{document}/download', [DossierController::class, 'downloadDocument'])
            ->name('download-document');
            
        // ✅ ROUTES TEMPLATES
        Route::get('/templates/adherents-excel', [DossierController::class, 'downloadTemplate'])
            ->name('templates.adherents-excel');
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
        Route::get('/', function () { return view('operator.reports.index'); })->name('index');
        Route::get('/organisation', function () { return view('operator.reports.organisation'); })->name('organisation');
        Route::get('/dossiers', function () { return view('operator.reports.dossiers'); })->name('dossiers');
        Route::get('/adherents', function () { return view('operator.reports.adherents'); })->name('adherents');
    });
    
    // Subventions
    Route::prefix('grants')->name('grants.')->group(function () {
        Route::get('/', function () { return view('operator.grants.index'); })->name('index');
        Route::get('/demandes', function () { return view('operator.grants.demandes'); })->name('demandes');
        Route::get('/historique', function () { return view('operator.grants.historique'); })->name('historique');
    });
    
    // Aide
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', function () { return view('operator.help.index'); })->name('index');
        Route::get('/guide', function () { return view('operator.help.guide'); })->name('guide');
        Route::get('/faq', function () { return view('operator.help.faq'); })->name('faq');
        Route::get('/contact', function () { return view('operator.help.contact'); })->name('contact');
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
    
    /*
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\MessageController::class, 'notifications'])->name('index');
        Route::post('/mark-all-read', [\App\Http\Controllers\Operator\MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/count', [\App\Http\Controllers\Operator\MessageController::class, 'unreadCount'])->name('count');
    });
    */
    
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
    
    Route::get('/verrous/status', function () {
        return response()->json([
            'locks_actifs' => 0,
            'dernier_nettoyage' => cache('locks_last_cleanup', 'Jamais')
        ]);
    })->name('verrous.status');
});



/*
|--------------------------------------------------------------------------
| Routes API pour Validation en Temps Réel
|--------------------------------------------------------------------------
*/
Route::prefix('api/v1')->name('api.')->middleware(['auth', 'throttle:60,1'])->group(function () {
    
    // Vérification NIP gabonais
    Route::post('/verify-nip', function (Request $request) {
        $request->validate([
            'nip' => 'required|string|size:13|regex:/^[0-9]{13}$/'
        ]);
        
        $nip = $request->input('nip');
        $exists = \App\Models\User::where('nip', $nip)->exists() ||
                 \App\Models\Adherent::where('nip', $nip)->exists() ||
                 \App\Models\Fondateur::where('nip', $nip)->exists();
        
        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Ce NIP est déjà utilisé' : 'NIP disponible'
        ]);
    })->name('verify-nip');
    
    // Vérification nom organisation
    Route::post('/verify-organization-name', function (Request $request) {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'type' => 'required|in:association,ong,parti_politique,confession_religieuse',
            'suggest_alternatives' => 'boolean'
        ]);
        
        $name = $request->input('name');
        $type = $request->input('type');
        $suggestAlternatives = $request->input('suggest_alternatives', false);
        
        $exists = \App\Models\Organisation::where('nom', $name)->where('type', $type)->exists();
        
        $response = [
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Ce nom est déjà utilisé pour ce type d\'organisation' : 'Nom disponible'
        ];
        
        if ($suggestAlternatives && $exists) {
            $suggestions = [];
            for ($i = 1; $i <= 3; $i++) {
                $suggestion = $name . ' ' . $i;
                if (!\App\Models\Organisation::where('nom', $suggestion)->where('type', $type)->exists()) {
                    $suggestions[] = $suggestion;
                }
            }
            $response['suggestions'] = array_slice($suggestions, 0, 5);
        }
        
        return response()->json($response);
    })->name('verify-organization-name');
    
    // Upload de document
    Route::post('/upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string',
            'organization_id' => 'nullable|exists:organisations,id'
        ]);
        
        $file = $request->file('file');
        $documentType = $request->input('document_type');
        $fileName = time() . '_' . $documentType . '.' . $file->getClientOriginalExtension();
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
    
    // Validation NIP
    Route::post('/validate-nip', [OrganisationController::class, 'validateNipApi'])->name('validate-nip');
    
    // Génération exemples NIP
    Route::get('/generate-nip-example', function () {
        try {
            $examples = [];
            $prefixes = ['A1', 'B2', 'C3', '1A', '2B', '3C'];
            $sequences = ['0001', '1234', '5678', '9999'];

            foreach (range(1, 5) as $i) {
                $prefix = $prefixes[array_rand($prefixes)];
                $sequence = $sequences[array_rand($sequences)];
                $year = rand(1960, 2005);
                $month = rand(1, 12);
                $day = rand(1, 28);
                $dateStr = sprintf('%04d%02d%02d', $year, $month, $day);
                $example = $prefix . '-' . $sequence . '-' . $dateStr;

                $examples[] = [
                    'nip' => $example,
                    'prefix' => $prefix,
                    'sequence' => $sequence,
                    'birth_date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                    'age' => now()->diffInYears(\Carbon\Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day)))
                ];
            }

            return response()->json([
                'success' => true,
                'examples' => $examples,
                'format' => 'XX-QQQQ-YYYYMMDD',
                'description' => [
                    'XX' => '2 caractères alphanumériques',
                    'QQQQ' => '4 chiffres',
                    'YYYYMMDD' => 'Date de naissance (ANNÉE MOIS JOUR)'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur génération exemples',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('generate-nip-example');
});

/*
|--------------------------------------------------------------------------
| Routes pour gestion CSRF et diagnostics
|--------------------------------------------------------------------------
*/
// ✅ NOUVEAU CODE CORRIGÉ :
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token(),                    // ✅ 'token' au lieu de 'csrf_token'
        'csrf_token' => csrf_token(),               // ✅ Compatibilité double
        'expires_at' => now()->addMinutes(config('session.lifetime'))->toISOString(),
        'timestamp' => now()->toISOString(),
        'session_lifetime' => config('session.lifetime')
    ]);
})->middleware('web');  // ✅ 'web' au lieu de 'auth' pour permettre l'accès sans authentification

// ========================================================================
// ROUTES ADDITIONNELLES POUR DEBUG CSRF (OPTIONNEL)
// ========================================================================

// Route de diagnostic CSRF (pour debug uniquement)
Route::get('/csrf-debug', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'session_lifetime' => config('session.lifetime'),
        'session_cookie' => config('session.cookie'),
        'app_key_set' => !empty(config('app.key')),
        'user_authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'middleware_applied' => 'web',
        'timestamp' => now()->toISOString()
    ]);
})->middleware('web');

// Route de test CSRF POST (pour vérifier que le token fonctionne)
Route::post('/csrf-test', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Token CSRF valide',
        'token_received' => $request->input('_token') ? 'Présent' : 'Absent',
        'timestamp' => now()->toISOString()
    ]);
})->middleware('web');


// Routes de test (développement uniquement)
if (config('app.debug')) {
    Route::get('/test', function () {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'database_connected' => DB::connection()->getPdo() ? 'Yes' : 'No',
            'current_user' => auth()->check() ? auth()->user()->email : 'Non connecté',
        ];
    })->name('test');
    
    Route::get('/create-test-users', function () {
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

        return 'Utilisateur de test créé !<br><strong>Opérateur :</strong> operator@pngdi.ga / operator123<br><a href="/login">Se connecter</a>';
    })->name('create-test-users');
}

// Inclure les routes supplémentaires
if (file_exists(__DIR__.'/admin.php')) {
    require __DIR__.'/admin.php';
}
if (file_exists(__DIR__.'/operator.php')) {
    require __DIR__.'/operator.php';
}