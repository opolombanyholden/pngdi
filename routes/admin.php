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
| Elles ne redéfinissent PAS la route /admin principale
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    
    // La route dashboard est déjà définie dans web.php, on ne la redéfinit pas ici
    
    // Gestion des dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        // La route index est déjà définie dans web.php
        Route::get('/en-attente', [DossierController::class, 'enAttente'])->name('en-attente');
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::post('/{dossier}/valider', [DossierController::class, 'valider'])->name('valider');
        Route::post('/{dossier}/rejeter', [DossierController::class, 'rejeter'])->name('rejeter');
        Route::post('/{dossier}/demander-complement', [DossierController::class, 'demanderComplement'])->name('complement');
        Route::post('/{dossier}/attribuer', [DossierController::class, 'attribuer'])->name('attribuer');
        Route::post('/{dossier}/archiver', [DossierController::class, 'archiver'])->name('archiver');
    });
    
    // Gestion des utilisateurs (admin uniquement)
    Route::prefix('users')->name('users.')->group(function () {
        // La route index est déjà définie dans web.php
        // On ajoute la vérification admin dans chaque action du contrôleur
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
    });
    
    // Gestion des référentiels
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        
        // Types d'organisations
        Route::prefix('types-organisations')->name('types.')->group(function () {
            Route::get('/', [ReferentielController::class, 'typesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'typesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'typesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'typesDestroy'])->name('destroy');
        });
        
        // Zones géographiques
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', [ReferentielController::class, 'zonesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'zonesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'zonesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'zonesDestroy'])->name('destroy');
        });
        
        // Statuts
        Route::prefix('statuts')->name('statuts.')->group(function () {
            Route::get('/', [ReferentielController::class, 'statutsIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'statutsStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'statutsUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'statutsDestroy'])->name('destroy');
        });
    });
    
    // Gestion du contenu
    Route::prefix('contenu')->name('contenu.')->group(function () {
        // Actualités
        Route::resource('actualites', ContentController::class);
        Route::post('actualites/{actualite}/toggle-publish', [ContentController::class, 'togglePublish'])->name('actualites.toggle-publish');
        
        // Documents publics
        Route::get('documents', [ContentController::class, 'documentsIndex'])->name('documents.index');
        Route::post('documents', [ContentController::class, 'documentsStore'])->name('documents.store');
        Route::delete('documents/{id}', [ContentController::class, 'documentsDestroy'])->name('documents.destroy');
        
        // FAQ
        Route::get('faq', [ContentController::class, 'faqIndex'])->name('faq.index');
        Route::post('faq', [ContentController::class, 'faqStore'])->name('faq.store');
        Route::put('faq/{id}', [ContentController::class, 'faqUpdate'])->name('faq.update');
        Route::delete('faq/{id}', [ContentController::class, 'faqDestroy'])->name('faq.destroy');
        
        // Événements calendrier
        Route::get('evenements', [ContentController::class, 'evenementsIndex'])->name('evenements.index');
        Route::post('evenements', [ContentController::class, 'evenementsStore'])->name('evenements.store');
        Route::put('evenements/{id}', [ContentController::class, 'evenementsUpdate'])->name('evenements.update');
        Route::delete('evenements/{id}', [ContentController::class, 'evenementsDestroy'])->name('evenements.destroy');
    });
    
    // Statistiques et rapports
    Route::prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('/', [DashboardController::class, 'statistiques'])->name('index');
        Route::get('/export', [DashboardController::class, 'export'])->name('export');
        Route::post('/rapport', [DashboardController::class, 'generateRapport'])->name('rapport');
    });
    
    // Journalisation (admin uniquement)
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [DashboardController::class, 'logs'])->name('index');
        Route::get('/export', [DashboardController::class, 'exportLogs'])->name('export');
    });
    
    // Sanctions
    Route::prefix('sanctions')->name('sanctions.')->group(function () {
        Route::get('/', [DossierController::class, 'sanctionsIndex'])->name('index');
        Route::post('/create', [DossierController::class, 'sanctionCreate'])->name('create');
        Route::post('/{sanction}/lever', [DossierController::class, 'sanctionLever'])->name('lever');
    });
});