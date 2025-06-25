<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DossierController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReferentielController;
use App\Http\Controllers\Admin\ContentController;

/*
|--------------------------------------------------------------------------
| Routes Administration - Complémentaires à web.php
|--------------------------------------------------------------------------
| Ces routes complètent celles définies dans web.php
| ⚠️ LA ROUTE /admin ET /admin/dashboard SONT DANS web.php
| ⚠️ NE PAS LES REDÉFINIR ICI
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | 📋 GESTION DES DOSSIERS DE VALIDATION
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        // Routes de listing et filtrage
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
        
        // Gestion des verrous (admin uniquement)
        Route::post('/{dossier}/force-unlock', [DossierController::class, 'forceUnlock'])
            ->name('force-unlock');
        Route::get('/locks/status', [DossierController::class, 'locksStatus'])
            ->name('locks.status');
        Route::post('/locks/clean-expired', [DossierController::class, 'cleanExpiredLocks'])
            ->name('locks.clean');
        
        // Rapports et exports
        Route::get('/export/excel', [DossierController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [DossierController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/rapport/generer', [DossierController::class, 'genererRapport'])->name('rapport.generer');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 👥 GESTION DES UTILISATEURS (Admin uniquement)
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
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
    
    /*
    |--------------------------------------------------------------------------
    | ⚙️ GESTION DES RÉFÉRENTIELS
    |--------------------------------------------------------------------------
    */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        
        // Types d'organisations
        Route::prefix('types-organisations')->name('types.')->group(function () {
            Route::get('/', [ReferentielController::class, 'typesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'typesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'typesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'typesDestroy'])->name('destroy');
            Route::post('/reorder', [ReferentielController::class, 'typesReorder'])->name('reorder');
        });
        
        // Zones géographiques (provinces, départements, communes)
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
        
        // Types de documents
        Route::prefix('documents-types')->name('documents.')->group(function () {
            Route::get('/', [ReferentielController::class, 'documentsIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'documentsStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'documentsUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'documentsDestroy'])->name('destroy');
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
    | 📝 GESTION DU CONTENU PUBLIC
    |--------------------------------------------------------------------------
    */
    Route::prefix('contenu')->name('contenu.')->group(function () {
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
    | 📊 STATISTIQUES ET RAPPORTS
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
    | 📋 JOURNALISATION ET AUDIT (Admin uniquement)
    |--------------------------------------------------------------------------
    */
    Route::prefix('logs')->name('logs.')->group(function () {
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
    Route::prefix('sanctions')->name('sanctions.')->group(function () {
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
    Route::prefix('system')->name('system.')->group(function () {
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
    Route::prefix('communications')->name('communications.')->group(function () {
        Route::get('/', [ContentController::class, 'communicationsIndex'])->name('index');
        Route::get('/create', [ContentController::class, 'communicationCreate'])->name('create');
        Route::post('/', [ContentController::class, 'communicationStore'])->name('store');
        Route::get('/{communication}', [ContentController::class, 'communicationShow'])->name('show');
        
        // Envoi de communications de masse
        Route::post('/send-bulk', [ContentController::class, 'sendBulkCommunication'])->name('send-bulk');
        Route::get('/templates', [ContentController::class, 'emailTemplates'])->name('templates');
        Route::post('/templates', [ContentController::class, 'saveEmailTemplate'])->name('templates.store');
    });
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
});