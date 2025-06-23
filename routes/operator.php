<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\ProfileController;
use App\Http\Controllers\Operator\DossierController;
use App\Http\Controllers\Operator\DeclarationController;
use App\Http\Controllers\Operator\MessageController;

/*
|--------------------------------------------------------------------------
| Routes Opérateurs - Complémentaires à web.php
|--------------------------------------------------------------------------
| Ces routes complètent celles définies dans web.php
| Elles ne redéfinissent PAS la route /operator principale
*/

Route::prefix('operator')->name('operator.')->middleware(['auth', 'verified'])->group(function () {
    
    // La route dashboard est déjà définie dans web.php, on ne la redéfinit pas ici
    
    // Profil
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });
    
    // Gestion des dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', [DossierController::class, 'index'])->name('index');
        
        // Création de nouvelles organisations
        Route::get('/create/{type}', [DossierController::class, 'create'])->name('create');
        Route::post('/store', [DossierController::class, 'store'])->name('store');
        
        // Gestion d'un dossier existant
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        Route::post('/{dossier}/soumettre', [DossierController::class, 'soumettre'])->name('soumettre');
        
        // Documents
        Route::post('/{dossier}/documents', [DossierController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/{dossier}/documents/{document}', [DossierController::class, 'deleteDocument'])->name('documents.delete');
        
        // Téléchargement des documents
        Route::get('/documents/{document}/download', [DossierController::class, 'downloadDocument'])->name('documents.download');
    });
    
    // Déclarations annuelles
    Route::prefix('declarations')->name('declarations.')->group(function () {
        Route::get('/', [DeclarationController::class, 'index'])->name('index');
        Route::get('/create/{organisation}', [DeclarationController::class, 'create'])->name('create');
        Route::post('/store', [DeclarationController::class, 'store'])->name('store');
        Route::get('/{declaration}', [DeclarationController::class, 'show'])->name('show');
        Route::get('/{declaration}/edit', [DeclarationController::class, 'edit'])->name('edit');
        Route::put('/{declaration}', [DeclarationController::class, 'update'])->name('update');
        Route::post('/{declaration}/soumettre', [DeclarationController::class, 'soumettre'])->name('soumettre');
        
        // Documents de déclaration
        Route::post('/{declaration}/documents', [DeclarationController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/{declaration}/documents/{document}', [DeclarationController::class, 'deleteDocument'])->name('documents.delete');
    });
    
    // Rapports d'activité
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [DeclarationController::class, 'rapportsIndex'])->name('index');
        Route::get('/create/{organisation}', [DeclarationController::class, 'rapportCreate'])->name('create');
        Route::post('/store', [DeclarationController::class, 'rapportStore'])->name('store');
        Route::get('/{rapport}', [DeclarationController::class, 'rapportShow'])->name('show');
    });
    
    // Demandes de subvention
    Route::prefix('subventions')->name('subventions.')->group(function () {
        Route::get('/', [DossierController::class, 'subventionsIndex'])->name('index');
        Route::get('/create/{organisation}', [DossierController::class, 'subventionCreate'])->name('create');
        Route::post('/store', [DossierController::class, 'subventionStore'])->name('store');
        Route::get('/{subvention}', [DossierController::class, 'subventionShow'])->name('show');
    });
    
    // Messagerie
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/nouveau', [MessageController::class, 'create'])->name('create');
        Route::post('/send', [MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [MessageController::class, 'reply'])->name('reply');
        Route::post('/{message}/mark-read', [MessageController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [MessageController::class, 'notifications'])->name('index');
        Route::post('/mark-all-read', [MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/count', [MessageController::class, 'unreadCount'])->name('count');
    });
    
    // Documents et guides
    Route::get('/guides', [ProfileController::class, 'guides'])->name('guides');
    Route::get('/documents-types', [ProfileController::class, 'documentsTypes'])->name('documents-types');
    
    // Calendrier des échéances
    Route::get('/calendrier', [ProfileController::class, 'calendrier'])->name('calendrier');

    // Organisations (à ajouter après la section Profil)
Route::prefix('organisations')->name('organisations.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('operator.dossiers.index');
        })->name('index');
    
    Route::get('/create', function () {
        return redirect()->route('operator.dossiers.create', 'association');
         })->name('create');
    });

});