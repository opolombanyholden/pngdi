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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}