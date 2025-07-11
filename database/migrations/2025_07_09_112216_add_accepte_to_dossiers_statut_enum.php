<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * ✅ MIGRATION SIMPLE : Ajouter 'accepte' à l'ENUM statut
 * 
 * PROBLÈME RÉSOLU :
 * - Data truncated for column 'statut' (valeur 'accepte' n'existe pas dans ENUM)
 * 
 * INSTRUCTIONS :
 * 1. Créer ce fichier : database/migrations/2025_07_09_111900_add_accepte_to_dossiers_statut_enum.php
 * 2. Exécuter : php artisan migrate
 */
class AddAccepteToDossiersStatutEnum extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ MODIFICATION DE L'ENUM : Ajouter 'accepte' aux valeurs existantes
        DB::statement("ALTER TABLE dossiers MODIFY COLUMN statut ENUM('brouillon','soumis','en_cours','approuve','rejete','accepte') NOT NULL DEFAULT 'brouillon'");
        
        // Log pour confirmer la migration
        \Log::info('✅ Migration ENUM statut appliquée - Ajout de "accepte"', [
            'table' => 'dossiers',
            'column' => 'statut',
            'new_values' => ['brouillon','soumis','en_cours','approuve','rejete','accepte']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retour à l'ENUM original sans 'accepte'
        DB::statement("ALTER TABLE dossiers MODIFY COLUMN statut ENUM('brouillon','soumis','en_cours','approuve','rejete') NOT NULL DEFAULT 'brouillon'");
        
        \Log::info('✅ Rollback migration ENUM statut - Suppression de "accepte"');
    }
}