<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Opérateurs
|--------------------------------------------------------------------------
| Routes réservées aux opérateurs (organisations)
*/

Route::prefix('operator')->name('operator.')->group(function () {  
    // Dashboard
    Route::get('/', 'Operator\ProfileController@dashboard')->name('dashboard');
    
    // Profil
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', 'Operator\ProfileController@index')->name('index');
        Route::get('/edit', 'Operator\ProfileController@edit')->name('edit');
        Route::put('/update', 'Operator\ProfileController@update')->name('update');
        Route::put('/password', 'Operator\ProfileController@updatePassword')->name('password');
    });
    
    // Gestion des dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', 'Operator\DossierController@index')->name('index');
        
        // Création de nouvelles organisations
        Route::get('/create/{type}', 'Operator\DossierController@create')->name('create');
        Route::post('/store', 'Operator\DossierController@store')->name('store');
        
        // Gestion d'un dossier existant
        Route::get('/{dossier}', 'Operator\DossierController@show')->name('show');
        Route::get('/{dossier}/edit', 'Operator\DossierController@edit')->name('edit');
        Route::put('/{dossier}', 'Operator\DossierController@update')->name('update');
        Route::post('/{dossier}/soumettre', 'Operator\DossierController@soumettre')->name('soumettre');
        
        // Documents
        Route::post('/{dossier}/documents', 'Operator\DossierController@uploadDocument')->name('documents.upload');
        Route::delete('/{dossier}/documents/{document}', 'Operator\DossierController@deleteDocument')->name('documents.delete');
        
        // Téléchargement des documents
        Route::get('/documents/{document}/download', 'Operator\DossierController@downloadDocument')->name('documents.download');
    });
    
    // Déclarations annuelles
    Route::prefix('declarations')->name('declarations.')->group(function () {
        Route::get('/', 'Operator\DeclarationController@index')->name('index');
        Route::get('/create/{organisation}', 'Operator\DeclarationController@create')->name('create');
        Route::post('/store', 'Operator\DeclarationController@store')->name('store');
        Route::get('/{declaration}', 'Operator\DeclarationController@show')->name('show');
        Route::get('/{declaration}/edit', 'Operator\DeclarationController@edit')->name('edit');
        Route::put('/{declaration}', 'Operator\DeclarationController@update')->name('update');
        Route::post('/{declaration}/soumettre', 'Operator\DeclarationController@soumettre')->name('soumettre');
        
        // Documents de déclaration
        Route::post('/{declaration}/documents', 'Operator\DeclarationController@uploadDocument')->name('documents.upload');
        Route::delete('/{declaration}/documents/{document}', 'Operator\DeclarationController@deleteDocument')->name('documents.delete');
    });
    
    // Rapports d'activité
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', 'Operator\DeclarationController@rapportsIndex')->name('index');
        Route::get('/create/{organisation}', 'Operator\DeclarationController@rapportCreate')->name('create');
        Route::post('/store', 'Operator\DeclarationController@rapportStore')->name('store');
        Route::get('/{rapport}', 'Operator\DeclarationController@rapportShow')->name('show');
    });
    
    // Demandes de subvention
    Route::prefix('subventions')->name('subventions.')->group(function () {
        Route::get('/', 'Operator\DossierController@subventionsIndex')->name('index');
        Route::get('/create/{organisation}', 'Operator\DossierController@subventionCreate')->name('create');
        Route::post('/store', 'Operator\DossierController@subventionStore')->name('store');
        Route::get('/{subvention}', 'Operator\DossierController@subventionShow')->name('show');
    });
    
    // Messagerie
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', 'Operator\MessageController@index')->name('index');
        Route::get('/nouveau', 'Operator\MessageController@create')->name('create');
        Route::post('/send', 'Operator\MessageController@store')->name('store');
        Route::get('/{message}', 'Operator\MessageController@show')->name('show');
        Route::post('/{message}/reply', 'Operator\MessageController@reply')->name('reply');
        Route::post('/{message}/mark-read', 'Operator\MessageController@markAsRead')->name('mark-read');
        Route::delete('/{message}', 'Operator\MessageController@destroy')->name('destroy');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', 'Operator\MessageController@notifications')->name('index');
        Route::post('/mark-all-read', 'Operator\MessageController@markAllAsRead')->name('mark-all-read');
        Route::get('/count', 'Operator\MessageController@unreadCount')->name('count');
    });
    
    // Documents et guides
    Route::get('/guides', 'Operator\ProfileController@guides')->name('guides');
    Route::get('/documents-types', 'Operator\ProfileController@documentsTypes')->name('documents-types');
    
    // Calendrier des échéances
    Route::get('/calendrier', 'Operator\ProfileController@calendrier')->name('calendrier');
});