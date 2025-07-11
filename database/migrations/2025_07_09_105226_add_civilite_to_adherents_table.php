<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ MIGRATION CRITIQUE : Ajouter colonne civilite manquante
 * 
 * PROBLÈME RÉSOLU :
 * - Column not found: 1054 Unknown column 'civilite' in 'field list'
 * 
 * INSTRUCTIONS :
 * 1. Créer ce fichier : database/migrations/2025_07_09_103500_add_civilite_to_adherents_table.php
 * 2. Exécuter : php artisan migrate
 */
class AddCiviliteToAdherentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            // Ajouter la colonne civilite après prenom
            $table->enum('civilite', ['M', 'Mme', 'Mlle'])
                  ->default('M')
                  ->after('prenom')
                  ->comment('Civilité de l\'adhérent');
            
            // Log pour confirmer la migration
            \Log::info('✅ Migration civilite appliquée à la table adherents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->dropColumn('civilite');
        });
    }
}