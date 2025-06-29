<?php

/**
 * 📄 CONTENU POUR LE FICHIER : add_profession_fonction_to_adherents_table.php
 * 
 * LOCALISATION : database/migrations/YYYY_MM_DD_XXXXXX_add_profession_fonction_to_adherents_table.php
 * 
 * INSTRUCTIONS :
 * 1. Exécuter : php artisan make:migration add_profession_fonction_to_adherents_table --table=adherents
 * 2. Ouvrir le fichier créé dans database/migrations/
 * 3. Remplacer tout le contenu par le code ci-dessous
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adherents', function (Blueprint $table) {
            // Ajouter colonne profession après email
            $table->string('profession')->nullable()->after('email')
                ->comment('Profession de l\'adhérent');
            
            // Ajouter colonne fonction après profession  
            $table->string('fonction')->default('Membre')->after('profession')
                ->comment('Fonction de l\'adhérent dans l\'organisation');
                
            // Index pour recherches fréquentes sur profession et fonction
            $table->index('profession', 'idx_adherents_profession');
            $table->index('fonction', 'idx_adherents_fonction');
        });
        
        // Log de la migration pour suivi
        \Log::info('Migration adherents: Colonnes profession et fonction ajoutées avec succès');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adherents', function (Blueprint $table) {
            // Supprimer les index d'abord pour éviter les erreurs
            $table->dropIndex('idx_adherents_profession');
            $table->dropIndex('idx_adherents_fonction');
            
            // Supprimer les colonnes
            $table->dropColumn(['profession', 'fonction']);
        });
        
        \Log::info('Migration adherents: Colonnes profession et fonction supprimées (rollback)');
    }
};