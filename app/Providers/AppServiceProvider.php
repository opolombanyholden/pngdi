<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OrganisationStepService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrement du service de gestion des étapes
        $this->app->singleton(OrganisationStepService::class, function ($app) {
            return new OrganisationStepService();
        });

        $this->app->singleton(\App\Services\PDFService::class, function ($app) {
            return new \App\Services\PDFService();
        });

        // ✅ ENREGISTREMENT CORRECT DES SERVICES
        $this->app->singleton(\App\Services\AnomalieService::class, function ($app) {
            return new \App\Services\AnomalieService();
        });
        
        // ✅ NOUVEAU : Enregistrement conditionnel de NipValidationService
        if (class_exists(\App\Services\NipValidationService::class)) {
            $this->app->singleton(\App\Services\NipValidationService::class, function ($app) {
                return new \App\Services\NipValidationService();
            });
        }


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}