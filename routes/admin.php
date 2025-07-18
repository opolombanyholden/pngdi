<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DossierController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReferentielController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\NipDatabaseController;

/*
|--------------------------------------------------------------------------
| Routes Administration - SGLP/PNGDI - VERSION COMPLÈTE ET DÉFINITIVE
|--------------------------------------------------------------------------
| Routes pour l'interface d'administration complète
| Middleware : auth, verified, admin
| Toutes les routes manquantes ajoutées pour résoudre les erreurs
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | 🏠 DASHBOARD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    /*
    |--------------------------------------------------------------------------
    | 📊 ANALYTICS ET RAPPORTS - SECTION COMPLÈTE
    |--------------------------------------------------------------------------
    */
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports.index');
    Route::get('/exports', [AnalyticsController::class, 'exports'])->name('exports.index');
    Route::get('/activity-logs', [AnalyticsController::class, 'activityLogs'])->name('activity-logs.index');

    // 📤 EXPORTS - Routes complètes
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'exports'])->name('index');
        Route::get('/global', [AnalyticsController::class, 'exportGlobal'])->name('global');
        Route::get('/dossiers', [AnalyticsController::class, 'exportDossiers'])->name('dossiers');
        Route::get('/users', [AnalyticsController::class, 'exportUsers'])->name('users');
        Route::get('/organisations', [AnalyticsController::class, 'exportOrganisations'])->name('organisations');
    });

    // 📊 REPORTS - Routes complètes  
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'reports'])->name('index');
        Route::get('/monthly', [AnalyticsController::class, 'monthlyReport'])->name('monthly');
        Route::get('/annual', [AnalyticsController::class, 'annualReport'])->name('annual');
        Route::get('/custom', [AnalyticsController::class, 'customReport'])->name('custom');
    });

    // 📈 ACTIVITY LOGS - Routes complètes
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'activityLogs'])->name('index');
        Route::get('/search', [AnalyticsController::class, 'searchLogs'])->name('search');
        Route::delete('/clean', [AnalyticsController::class, 'cleanLogs'])->name('clean');
        Route::get('/export', [AnalyticsController::class, 'exportLogs'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔄 WORKFLOW DES DOSSIERS - ROUTES CORRIGÉES
    |--------------------------------------------------------------------------
    */
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/en-attente', [WorkflowController::class, 'enAttente'])->name('en-attente');
        Route::get('/en-cours', [WorkflowController::class, 'enCours'])->name('en-cours');
        Route::get('/termines', [WorkflowController::class, 'termines'])->name('termines');
        
        // Actions workflow
        Route::post('/{dossier}/assign', [WorkflowController::class, 'assign'])->name('assign');
        Route::post('/{dossier}/validate', [WorkflowController::class, 'validateDossier'])->name('validate');
        Route::post('/{dossier}/reject', [WorkflowController::class, 'reject'])->name('reject');
    });

    /*
    |--------------------------------------------------------------------------
    | 🏢 GESTION DES ORGANISATIONS - ROUTE CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::get('/organisations', [DossierController::class, 'index'])->name('organisations.index');

    /*
    |--------------------------------------------------------------------------
    | 🔔 NOTIFICATIONS - ROUTES CORRIGÉES (SANS CONFLIT)
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    /*
    |--------------------------------------------------------------------------
    | ⚙️ PARAMÈTRES SYSTÈME - ROUTES CORRIGÉES
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
        Route::get('/system-info', [SettingsController::class, 'systemInfo'])->name('system-info');
    });

    /*
    |--------------------------------------------------------------------------
    | 📋 GESTION DES DOSSIERS DE VALIDATION
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        // Routes de listing et filtrage
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/en-attente', [DossierController::class, 'enAttente'])->name('en-attente');
        Route::get('/valides', [DossierController::class, 'valides'])->name('valides');
        Route::get('/rejetes', [DossierController::class, 'rejetes'])->name('rejetes');
        Route::get('/archives', [DossierController::class, 'archives'])->name('archives');
        
        // Actions sur un dossier spécifique
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::post('/{dossier}/valider', [DossierController::class, 'valider'])
            ->middleware(['dossier.lock:validate'])
            ->name('valider');
        Route::post('/{dossier}/rejeter', [DossierController::class, 'rejeter'])
            ->middleware(['dossier.lock:validate'])
            ->name('rejeter');
        Route::post('/{dossier}/demander-complement', [DossierController::class, 'demanderComplement'])
            ->name('complement');
        Route::post('/{dossier}/attribuer', [DossierController::class, 'attribuer'])
            ->name('attribuer');
        Route::post('/{dossier}/archiver', [DossierController::class, 'archiver'])
            ->name('archiver');

        // Routes de téléchargement PDF
        Route::get('/{dossier}/download-accuse', [DossierController::class, 'downloadAccuse'])->name('download-accuse');
        Route::get('/{dossier}/download-recepisse', [DossierController::class, 'downloadRecepisse'])->name('download-recepisse');
        Route::get('/{dossier}/download-recepisse-provisoire', [DossierController::class, 'downloadRecepisseProvisoire'])
            ->name('download-recepisse-provisoire');


        // Gestion des verrous (admin uniquement)
        Route::middleware('admin.only')->group(function () {
            Route::post('/{dossier}/force-unlock', [DossierController::class, 'forceUnlock'])
                ->name('force-unlock');
            Route::get('/locks/status', [DossierController::class, 'locksStatus'])
                ->name('locks.status');
            Route::post('/locks/clean-expired', [DossierController::class, 'cleanExpiredLocks'])
                ->name('locks.clean');
        });
        
        // Rapports et exports
        Route::get('/export/excel', [DossierController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [DossierController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/rapport/generer', [DossierController::class, 'genererRapport'])->name('rapport.generer');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 👥 GESTION DES UTILISATEURS - SECTION CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        // Routes principales pour le menu admin (noms cohérents avec layout)
        Route::get('/operators', [UserManagementController::class, 'operators'])->name('operators');
        Route::get('/agents', [UserManagementController::class, 'agents'])->name('agents');
        
        // Routes CRUD complètes
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        
        // Actions spéciales
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
            ->name('toggle-status');
        Route::post('/{user}/update-status', [UserManagementController::class, 'updateStatus'])
            ->name('update-status');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->name('reset-password');
        Route::post('/{user}/force-verify-email', [UserManagementController::class, 'forceVerifyEmail'])
            ->name('force-verify-email');
        Route::post('/{user}/disable-2fa', [UserManagementController::class, 'disable2FA'])
            ->name('disable-2fa');
        Route::post('/{user}/send-welcome', [UserManagementController::class, 'sendWelcomeEmail'])
            ->name('send-welcome');
        
        // Import/Export utilisateurs
        Route::get('/export/excel', [UserManagementController::class, 'exportExcel'])->name('export.excel');
        Route::post('/import', [UserManagementController::class, 'import'])->name('import');
        Route::get('/import/template', [UserManagementController::class, 'downloadTemplate'])
            ->name('import.template');
    });


    // Module Gestion Base NIP
    Route::prefix('nip-database')->name('nip-database.')->group(function () {
        Route::get('/', [NipDatabaseController::class, 'index'])->name('index');
        Route::get('/import', [NipDatabaseController::class, 'import'])->name('import');
        Route::post('/import', [NipDatabaseController::class, 'processImport'])->name('process-import');
        Route::get('/template', [NipDatabaseController::class, 'downloadTemplate'])->name('template');
        Route::get('/export', [NipDatabaseController::class, 'export'])->name('export');
        Route::post('/cleanup', [NipDatabaseController::class, 'cleanup'])->name('cleanup');
        Route::get('/search', [NipDatabaseController::class, 'search'])->name('search');
        Route::post('/verify', [NipDatabaseController::class, 'verify'])->name('verify');
        Route::get('/{id}', [NipDatabaseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [NipDatabaseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NipDatabaseController::class, 'update'])->name('update');
        Route::delete('/{id}', [NipDatabaseController::class, 'destroy'])->name('destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 🏗️ GESTION DES RÉFÉRENTIELS - SECTION ENTIÈREMENT CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        // Route index principale
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        
        // ⚡ ROUTES SIMPLES OBLIGATOIRES POUR LE MENU ADMIN - EN PREMIER
        Route::get('/types-organisations', [ReferentielController::class, 'typesOrganisations'])->name('types-organisations');
        Route::get('/document-types', [ReferentielController::class, 'documentTypes'])->name('document-types');
        Route::get('/zones', [ReferentielController::class, 'zones'])->name('zones');
        
        // CRUD Types d'organisations
        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', [ReferentielController::class, 'typesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'typesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'typesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'typesDestroy'])->name('destroy');
            Route::post('/reorder', [ReferentielController::class, 'typesReorder'])->name('reorder');
        });
        
        // CRUD Types de documents
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [ReferentielController::class, 'documentsIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'documentsStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'documentsUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'documentsDestroy'])->name('destroy');
        });
        
        // CRUD Zones géographiques
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', [ReferentielController::class, 'zonesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'zonesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'zonesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'zonesDestroy'])->name('destroy');
            Route::get('/provinces/{province}/departements', [ReferentielController::class, 'getDepartements'])
                ->name('provinces.departements');
            Route::get('/departements/{departement}/communes', [ReferentielController::class, 'getCommunes'])
                ->name('departements.communes');
        });
        
        // Statuts des dossiers/organisations
        Route::prefix('statuts')->name('statuts.')->group(function () {
            Route::get('/', [ReferentielController::class, 'statutsIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'statutsStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'statutsUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'statutsDestroy'])->name('destroy');
        });
        
        // Workflow et étapes de validation
        Route::prefix('workflow')->name('workflow.')->group(function () {
            Route::get('/', [ReferentielController::class, 'workflowIndex'])->name('index');
            Route::post('/steps', [ReferentielController::class, 'workflowStepStore'])->name('steps.store');
            Route::put('/steps/{id}', [ReferentielController::class, 'workflowStepUpdate'])->name('steps.update');
            Route::delete('/steps/{id}', [ReferentielController::class, 'workflowStepDestroy'])->name('steps.destroy');
            Route::post('/steps/reorder', [ReferentielController::class, 'workflowStepsReorder'])->name('steps.reorder');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📝 GESTION DU CONTENU PUBLIC - SECTION CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('content')->name('content.')->group(function () {
        // Routes principales pour le menu
        Route::get('/actualites', [ContentController::class, 'actualites'])->name('actualites');
        Route::get('/documents', [ContentController::class, 'documents'])->name('documents');
        Route::get('/faq', [ContentController::class, 'faq'])->name('faq');
        
        // Actions contenu
        Route::get('/actualites/create', [ContentController::class, 'createActualite'])->name('actualites.create');
        Route::post('/documents/upload', [ContentController::class, 'uploadDocument'])->name('documents.upload');
        Route::post('/faq/create', [ContentController::class, 'createFaq'])->name('faq.create');
    });

    // Routes contenu avancées (gardées séparément pour compatibilité)
    Route::prefix('contenu')->name('contenu.')->middleware('admin.only')->group(function () {
        // Actualités
        Route::resource('actualites', ContentController::class);
        Route::post('actualites/{actualite}/toggle-publish', [ContentController::class, 'togglePublish'])
            ->name('actualites.toggle-publish');
        Route::post('actualites/{actualite}/duplicate', [ContentController::class, 'duplicate'])
            ->name('actualites.duplicate');
        
        // Documents publics
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [ContentController::class, 'documentsIndex'])->name('index');
            Route::post('/', [ContentController::class, 'documentsStore'])->name('store');
            Route::put('/{id}', [ContentController::class, 'documentsUpdate'])->name('update');
            Route::delete('/{id}', [ContentController::class, 'documentsDestroy'])->name('destroy');
            Route::post('/{id}/toggle-publish', [ContentController::class, 'documentsTogglePublish'])
                ->name('toggle-publish');
        });
        
        // FAQ
        Route::prefix('faq')->name('faq.')->group(function () {
            Route::get('/', [ContentController::class, 'faqIndex'])->name('index');
            Route::post('/', [ContentController::class, 'faqStore'])->name('store');
            Route::put('/{id}', [ContentController::class, 'faqUpdate'])->name('update');
            Route::delete('/{id}', [ContentController::class, 'faqDestroy'])->name('destroy');
            Route::post('/reorder', [ContentController::class, 'faqReorder'])->name('reorder');
        });
        
        // Événements calendrier
        Route::prefix('evenements')->name('evenements.')->group(function () {
            Route::get('/', [ContentController::class, 'evenementsIndex'])->name('index');
            Route::post('/', [ContentController::class, 'evenementsStore'])->name('store');
            Route::put('/{id}', [ContentController::class, 'evenementsUpdate'])->name('update');
            Route::delete('/{id}', [ContentController::class, 'evenementsDestroy'])->name('destroy');
            Route::get('/calendar-data', [ContentController::class, 'calendarData'])->name('calendar-data');
        });
        
        // Pages statiques
        Route::prefix('pages')->name('pages.')->group(function () {
            Route::get('/', [ContentController::class, 'pagesIndex'])->name('index');
            Route::post('/', [ContentController::class, 'pagesStore'])->name('store');
            Route::put('/{id}', [ContentController::class, 'pagesUpdate'])->name('update');
            Route::delete('/{id}', [ContentController::class, 'pagesDestroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 📊 STATISTIQUES ET RAPPORTS - SECTION CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('/', [DashboardController::class, 'statistiques'])->name('index');
        Route::get('/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('dashboard-data');
        Route::get('/organisations', [DashboardController::class, 'statsOrganisations'])->name('organisations');
        Route::get('/dossiers', [DashboardController::class, 'statsDossiers'])->name('dossiers');
        Route::get('/utilisateurs', [DashboardController::class, 'statsUtilisateurs'])->name('utilisateurs');
        Route::get('/activite', [DashboardController::class, 'statsActivite'])->name('activite');
        
        // Exports
        Route::get('/export/global', [DashboardController::class, 'exportGlobal'])->name('export.global');
        Route::post('/rapport/personnalise', [DashboardController::class, 'generateCustomReport'])
            ->name('rapport.personnalise');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 👤 PROFIL ADMINISTRATEUR - ROUTES AJOUTÉES
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [SettingsController::class, 'profile'])->name('index');
        Route::put('/', [SettingsController::class, 'updateProfile'])->name('update');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password');
        Route::post('/avatar', [SettingsController::class, 'updateAvatar'])->name('avatar');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📋 JOURNALISATION ET AUDIT
    |--------------------------------------------------------------------------
    */
    Route::prefix('logs')->name('logs.')->middleware('admin.only')->group(function () {
        Route::get('/', [DashboardController::class, 'logs'])->name('index');
        Route::get('/search', [DashboardController::class, 'searchLogs'])->name('search');
        Route::get('/export', [DashboardController::class, 'exportLogs'])->name('export');
        Route::delete('/clean', [DashboardController::class, 'cleanOldLogs'])->name('clean');
        Route::get('/stats', [DashboardController::class, 'logsStats'])->name('stats');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ⚖️ SANCTIONS ET MESURES DISCIPLINAIRES
    |--------------------------------------------------------------------------
    */
    Route::prefix('sanctions')->name('sanctions.')->middleware('admin.only')->group(function () {
        Route::get('/', [DossierController::class, 'sanctionsIndex'])->name('index');
        Route::get('/create/{organisation}', [DossierController::class, 'sanctionCreate'])->name('create');
        Route::post('/', [DossierController::class, 'sanctionStore'])->name('store');
        Route::get('/{sanction}', [DossierController::class, 'sanctionShow'])->name('show');
        Route::post('/{sanction}/appliquer', [DossierController::class, 'sanctionAppliquer'])->name('appliquer');
        Route::post('/{sanction}/lever', [DossierController::class, 'sanctionLever'])->name('lever');
        Route::post('/{sanction}/reporter', [DossierController::class, 'sanctionReporter'])->name('reporter');
        
        // Types de sanctions
        Route::get('/types', [DossierController::class, 'sanctionTypes'])->name('types');
        Route::post('/types', [DossierController::class, 'sanctionTypeStore'])->name('types.store');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 🔧 CONFIGURATION SYSTÈME
    |--------------------------------------------------------------------------
    */
    Route::prefix('system')->name('system.')->middleware('admin.only')->group(function () {
        Route::get('/', [DashboardController::class, 'systemInfo'])->name('index');
        Route::get('/config', [DashboardController::class, 'systemConfig'])->name('config');
        Route::post('/config', [DashboardController::class, 'updateSystemConfig'])->name('config.update');
        
        // Maintenance
        Route::post('/maintenance/enable', [DashboardController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [DashboardController::class, 'disableMaintenance'])->name('maintenance.disable');
        Route::post('/cache/clear', [DashboardController::class, 'clearCache'])->name('cache.clear');
        Route::post('/storage/link', [DashboardController::class, 'storageLink'])->name('storage.link');
        
        // Sauvegardes
        Route::get('/backup', [DashboardController::class, 'backupIndex'])->name('backup.index');
        Route::post('/backup/create', [DashboardController::class, 'createBackup'])->name('backup.create');
        Route::get('/backup/{backup}/download', [DashboardController::class, 'downloadBackup'])->name('backup.download');
        Route::delete('/backup/{backup}', [DashboardController::class, 'deleteBackup'])->name('backup.delete');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📧 NOTIFICATIONS ET COMMUNICATIONS
    |--------------------------------------------------------------------------
    */
    Route::prefix('communications')->name('communications.')->middleware('admin.only')->group(function () {
        Route::get('/', [ContentController::class, 'communicationsIndex'])->name('index');
        Route::get('/create', [ContentController::class, 'communicationCreate'])->name('create');
        Route::post('/', [ContentController::class, 'communicationStore'])->name('store');
        Route::get('/{communication}', [ContentController::class, 'communicationShow'])->name('show');
        
        // Envoi de communications de masse
        Route::post('/send-bulk', [ContentController::class, 'sendBulkCommunication'])->name('send-bulk');
        Route::get('/templates', [ContentController::class, 'emailTemplates'])->name('templates');
        Route::post('/templates', [ContentController::class, 'saveEmailTemplate'])->name('templates.store');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 🔧 ROUTES TEMPORAIRES POUR DÉVELOPPEMENT - À SUPPRIMER EN PRODUCTION
    |--------------------------------------------------------------------------
    */
    Route::get('/config', function() {
        return response()->json(['message' => 'Configuration admin - À implémenter']);
    })->name('config.index');
    
    Route::get('/maintenance', function() {
        return response()->json(['message' => 'Mode maintenance - À implémenter']);
    })->name('maintenance.index');
});

/*
|--------------------------------------------------------------------------
| 🔗 Routes API Admin pour les interfaces AJAX
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')->name('api.admin.')->middleware(['auth', 'admin'])->group(function () {
    // Recherche rapide
    Route::get('/search/{type}', [DashboardController::class, 'quickSearch'])->name('search');
    
    // Statistiques temps réel
    Route::get('/stats/realtime', [DashboardController::class, 'realtimeStats'])->name('stats.realtime');
    
    // Vérifications système
    Route::get('/system/health', [DashboardController::class, 'systemHealth'])->name('system.health');
    
    // Gestion des verrous en temps réel
    Route::get('/locks/list', [DossierController::class, 'listActiveLocks'])->name('locks.list');
    Route::post('/locks/{lock}/release', [DossierController::class, 'releaseLock'])->name('locks.release');
    
    // Notifications
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    
    // API de validation NIP temps réel
    Route::post('/api/validate-nip', [App\Services\NipValidationService::class, 'validateNipApi'])
        ->name('api.validate-nip')
        ->middleware('auth');
    // === FIN BLOC À AJOUTER ===

// ===============================================
// AJOUTS À EFFECTUER DANS routes/admin.php
// ===============================================

// ============ DÉBUT DE BLOCK À AJOUTER ============
// Section: Gestion Dossiers - Actions spécifiques
Route::prefix('dossiers')->name('dossiers.')->group(function () {
    
    // Routes existantes à conserver
    Route::get('/en-attente', [DossierController::class, 'enAttente'])->name('en-attente');
    Route::get('/', [DossierController::class, 'index'])->name('index');
    Route::get('/{id}', [DossierController::class, 'show'])->name('show');
    
    // ========== NOUVELLES ROUTES À AJOUTER ==========
    
    // Assignation de dossiers
    Route::post('/{id}/assign', [DossierController::class, 'assign'])->name('assign');
    Route::post('/assign-multiple', [DossierController::class, 'assignMultiple'])->name('assign-multiple');
    
    // Commentaires sur dossiers
    Route::post('/{id}/comment', [DossierController::class, 'addComment'])->name('comment');
    Route::get('/{id}/comments', [DossierController::class, 'getComments'])->name('comments.list');
    
    // Actions de validation/rejet
    Route::post('/{id}/validate', [DossierController::class, 'validate'])->name('validate');
    Route::post('/{id}/reject', [DossierController::class, 'reject'])->name('reject');
    Route::post('/{id}/request-modification', [DossierController::class, 'requestModification'])->name('request-modification');
    
    // Gestion du verrouillage
    Route::post('/{id}/lock', [DossierController::class, 'lock'])->name('lock');
    Route::delete('/{id}/unlock', [DossierController::class, 'unlock'])->name('unlock');
    
    // Historique et audit
    Route::get('/{id}/history', [DossierController::class, 'history'])->name('history');
    Route::get('/{id}/timeline', [DossierController::class, 'timeline'])->name('timeline');
    
    // Documents associés
    Route::get('/{id}/documents', [DossierController::class, 'documents'])->name('documents');
    Route::get('/{id}/documents/{documentId}/download', [DossierController::class, 'downloadDocument'])->name('documents.download');
    Route::get('/{id}/documents/{documentId}/preview', [DossierController::class, 'previewDocument'])->name('documents.preview');
    
    // Export et impression
    Route::post('/export', [DossierController::class, 'export'])->name('export');
    Route::get('/{id}/print', [DossierController::class, 'print'])->name('print');
    Route::get('/{id}/pdf', [DossierController::class, 'generatePDF'])->name('pdf');
});

// ========== ROUTES WORKFLOW COMPLÉMENTAIRES ==========
Route::prefix('workflow')->name('workflow.')->group(function () {
    
    // États de workflow
    Route::get('/en-cours', [WorkflowController::class, 'enCours'])->name('en-cours');
    Route::get('/termines', [WorkflowController::class, 'termines'])->name('termines');
    Route::get('/rejetes', [WorkflowController::class, 'rejetes'])->name('rejetes');
    Route::get('/archives', [WorkflowController::class, 'archives'])->name('archives');
    
    // Actions sur workflow
    Route::post('/step/{stepId}/complete', [WorkflowController::class, 'completeStep'])->name('step.complete');
    Route::post('/step/{stepId}/skip', [WorkflowController::class, 'skipStep'])->name('step.skip');
    Route::post('/reset/{dossierId}', [WorkflowController::class, 'resetWorkflow'])->name('reset');
    
    // Configuration workflow
    Route::get('/templates', [WorkflowController::class, 'templates'])->name('templates');
    Route::post('/templates', [WorkflowController::class, 'saveTemplate'])->name('templates.save');
});

// ========== ROUTES EXPORTS SPÉCIALISÉES ==========
Route::prefix('exports')->name('exports.')->group(function () {
    
    // Exports de dossiers
    Route::post('/dossiers', [AnalyticsController::class, 'dossiers'])->name('dossiers');
    Route::post('/dossiers-en-attente', [AnalyticsController::class, 'dossiersEnAttente'])->name('dossiers-en-attente');
    Route::post('/dossiers-agent/{agentId}', [AnalyticsController::class, 'dossiersAgent'])->name('dossiers-agent');
    
    // Exports d'organisations
    Route::post('/organisations', [AnalyticsController::class, 'organisations'])->name('organisations');
    Route::post('/organisations-par-type', [AnalyticsController::class, 'organisationsParType'])->name('organisations-par-type');
    
    // Exports de rapports
    Route::post('/rapport-activite', [AnalyticsController::class, 'rapportActivite'])->name('rapport-activite');
    Route::post('/rapport-performance', [AnalyticsController::class, 'rapportPerformance'])->name('rapport-performance');
    Route::post('/statistiques', [AnalyticsController::class, 'statistiques'])->name('statistiques');
    
    // Formats multiples
    Route::get('/format/{type}/{format}', [AnalyticsController::class, 'downloadFormat'])
         ->name('format')
         ->where('format', 'excel|pdf|csv|json');
});

// ========== ROUTES API AJAX ==========
Route::prefix('api')->name('api.')->group(function () {
    
    // API pour datatables et filtres
    Route::get('/dossiers/search', [DossierController::class, 'apiSearch'])->name('dossiers.search');
    Route::get('/agents/available', [UserManagementController::class, 'availableAgents'])->name('agents.available');
    Route::get('/organisations/search', [DossierController::class, 'searchOrganisations'])->name('organisations.search');
    
    // API pour statistiques temps réel
    Route::get('/stats/dossiers', [AnalyticsController::class, 'dossiersStats'])->name('stats.dossiers');
    Route::get('/stats/agents', [AnalyticsController::class, 'agentsStats'])->name('stats.agents');
    Route::get('/stats/performance', [AnalyticsController::class, 'performanceStats'])->name('stats.performance');
    
    // API pour notifications
    Route::get('/notifications/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // API pour validations
    Route::get('/dossier/{id}/validation-status', [DossierController::class, 'validationStatus'])->name('dossier.validation-status');
    Route::post('/dossier/{id}/quick-action', [DossierController::class, 'quickAction'])->name('dossier.quick-action');
});

// ========== ROUTES BATCH OPERATIONS ==========
Route::prefix('batch')->name('batch.')->group(function () {
    
    // Actions en lot sur dossiers
    Route::post('/assign-multiple', [DossierController::class, 'batchAssign'])->name('assign-multiple');
    Route::post('/change-status', [DossierController::class, 'batchChangeStatus'])->name('change-status');
    Route::post('/add-comment', [DossierController::class, 'batchAddComment'])->name('add-comment');
    Route::post('/export-selected', [DossierController::class, 'batchExport'])->name('export-selected');
    
    // Actions en lot sur utilisateurs
    Route::post('/users/activate', [UserManagementController::class, 'batchActivate'])->name('users.activate');
    Route::post('/users/deactivate', [UserManagementController::class, 'batchDeactivate'])->name('users.deactivate');
    Route::post('/users/change-role', [UserManagementController::class, 'batchChangeRole'])->name('users.change-role');
});
// ============ FIN DE BLOCK À AJOUTER ============



});