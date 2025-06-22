<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Administration
|--------------------------------------------------------------------------
| Routes réservées aux administrateurs et agents
*/

Route::prefix('admin')->name('admin.')->group(function () {   
    // Dashboard
    Route::get('/', 'Admin\DashboardController@index')->name('dashboard');
    
    // Gestion des dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', 'Admin\DossierController@index')->name('index');
        Route::get('/en-attente', 'Admin\DossierController@enAttente')->name('en-attente');
        Route::get('/{dossier}', 'Admin\DossierController@show')->name('show');
        Route::post('/{dossier}/valider', 'Admin\DossierController@valider')->name('valider');
        Route::post('/{dossier}/rejeter', 'Admin\DossierController@rejeter')->name('rejeter');
        Route::post('/{dossier}/demander-complement', 'Admin\DossierController@demanderComplement')->name('complement');
        Route::post('/{dossier}/attribuer', 'Admin\DossierController@attribuer')->name('attribuer');
        Route::post('/{dossier}/archiver', 'Admin\DossierController@archiver')->name('archiver');
    });
    
    // Gestion des référentiels
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', 'Admin\ReferentielController@index')->name('index');
        
        // Types d'organisations
        Route::prefix('types-organisations')->name('types.')->group(function () {
            Route::get('/', 'Admin\ReferentielController@typesIndex')->name('index');
            Route::post('/', 'Admin\ReferentielController@typesStore')->name('store');
            Route::put('/{id}', 'Admin\ReferentielController@typesUpdate')->name('update');
            Route::delete('/{id}', 'Admin\ReferentielController@typesDestroy')->name('destroy');
        });
        
        // Zones géographiques
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', 'Admin\ReferentielController@zonesIndex')->name('index');
            Route::post('/', 'Admin\ReferentielController@zonesStore')->name('store');
            Route::put('/{id}', 'Admin\ReferentielController@zonesUpdate')->name('update');
            Route::delete('/{id}', 'Admin\ReferentielController@zonesDestroy')->name('destroy');
        });
        
        // Statuts
        Route::prefix('statuts')->name('statuts.')->group(function () {
            Route::get('/', 'Admin\ReferentielController@statutsIndex')->name('index');
            Route::post('/', 'Admin\ReferentielController@statutsStore')->name('store');
            Route::put('/{id}', 'Admin\ReferentielController@statutsUpdate')->name('update');
            Route::delete('/{id}', 'Admin\ReferentielController@statutsDestroy')->name('destroy');
        });
    });
    
    // Gestion du contenu
    Route::prefix('contenu')->name('contenu.')->group(function () {
        // Actualités
        Route::get('actualites', 'Admin\ContentController@index')->name('actualites.index');
        Route::get('actualites/create', 'Admin\ContentController@create')->name('actualites.create');
        Route::post('actualites', 'Admin\ContentController@store')->name('actualites.store');
        Route::get('actualites/{actualite}', 'Admin\ContentController@show')->name('actualites.show');
        Route::get('actualites/{actualite}/edit', 'Admin\ContentController@edit')->name('actualites.edit');
        Route::put('actualites/{actualite}', 'Admin\ContentController@update')->name('actualites.update');
        Route::delete('actualites/{actualite}', 'Admin\ContentController@destroy')->name('actualites.destroy');
        Route::post('actualites/{actualite}/toggle-publish', 'Admin\ContentController@togglePublish')->name('actualites.toggle-publish');
        
        // Documents publics
        Route::get('documents', 'Admin\ContentController@documentsIndex')->name('documents.index');
        Route::post('documents', 'Admin\ContentController@documentsStore')->name('documents.store');
        Route::delete('documents/{id}', 'Admin\ContentController@documentsDestroy')->name('documents.destroy');
        
        // FAQ
        Route::get('faq', 'Admin\ContentController@faqIndex')->name('faq.index');
        Route::post('faq', 'Admin\ContentController@faqStore')->name('faq.store');
        Route::put('faq/{id}', 'Admin\ContentController@faqUpdate')->name('faq.update');
        Route::delete('faq/{id}', 'Admin\ContentController@faqDestroy')->name('faq.destroy');
        
        // Événements calendrier
        Route::get('evenements', 'Admin\ContentController@evenementsIndex')->name('evenements.index');
        Route::post('evenements', 'Admin\ContentController@evenementsStore')->name('evenements.store');
        Route::put('evenements/{id}', 'Admin\ContentController@evenementsUpdate')->name('evenements.update');
        Route::delete('evenements/{id}', 'Admin\ContentController@evenementsDestroy')->name('evenements.destroy');
    });
    
    // Statistiques et rapports
    Route::prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('/', 'Admin\DashboardController@statistiques')->name('index');
        Route::get('/export', 'Admin\DashboardController@export')->name('export');
        Route::post('/rapport', 'Admin\DashboardController@generateRapport')->name('rapport');
    });
    
    // Journalisation
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', 'Admin\DashboardController@logs')->name('index');
        Route::get('/export', 'Admin\DashboardController@exportLogs')->name('export');
    });
    
    // Sanctions
    Route::prefix('sanctions')->name('sanctions.')->group(function () {
        Route::get('/', 'Admin\DossierController@sanctionsIndex')->name('index');
        Route::post('/create', 'Admin\DossierController@sanctionCreate')->name('create');
        Route::post('/{sanction}/lever', 'Admin\DossierController@sanctionLever')->name('lever');
    });
});