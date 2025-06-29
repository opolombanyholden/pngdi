<?php

/**
 * 📄 CONTENU POUR LE FICHIER : add_nom_prenom_to_users_table.php
 * 
 * LOCALISATION : database/migrations/YYYY_MM_DD_XXXXXX_add_nom_prenom_to_users_table.php
 * 
 * ⚠️ MIGRATION OPTIONNELLE : Améliore la structure users mais pas obligatoire
 * 
 * INSTRUCTIONS :
 * 1. Exécuter : php artisan make:migration add_nom_prenom_to_users_table --table=users
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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter nom après name
            $table->string('nom')->nullable()->after('name')
                ->comment('Nom de famille de l\'utilisateur');
                
            // Ajouter prenom après nom
            $table->string('prenom')->nullable()->after('nom')
                ->comment('Prénom de l\'utilisateur');
            
            // Index combiné pour recherches par nom/prenom
            $table->index(['nom', 'prenom'], 'idx_users_nom_prenom');
        });
        
        // Migration automatique des données existantes
        $this->migrateExistingUserNames();
        
        \Log::info('Migration users: Colonnes nom et prenom ajoutées avec migration des données');
    }

    /**
     * Migrer automatiquement les noms existants
     * Sépare le champ 'name' en 'nom' et 'prenom' quand c'est possible
     */
    private function migrateExistingUserNames()
    {
        try {
            // Récupérer tous les utilisateurs avec un name non vide
            $users = \App\Models\User::whereNotNull('name')
                ->where('name', '!=', '')
                ->whereNull('nom')  // Seulement ceux pas encore migrés
                ->get();
            
            $migratedCount = 0;
            
            foreach ($users as $user) {
                // Nettoyer et séparer le nom
                $fullName = trim($user->name);
                $nameParts = explode(' ', $fullName, 2);
                
                if (count($nameParts) >= 2) {
                    // Nom composé : "Jean DUPONT" -> nom="Jean", prenom="DUPONT"
                    $user->update([
                        'nom' => trim($nameParts[0]),
                        'prenom' => trim($nameParts[1])
                    ]);
                } else {
                    // Nom simple : "Jean" -> nom="Jean", prenom=null
                    $user->update([
                        'nom' => $fullName,
                        'prenom' => null
                    ]);
                }
                
                $migratedCount++;
            }
            
            \Log::info("Migration users: {$migratedCount} noms d'utilisateurs migrés automatiquement");
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la migration des noms d\'utilisateurs: ' . $e->getMessage());
            // Ne pas faire échouer la migration pour cette erreur
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer l'index combiné
            $table->dropIndex('idx_users_nom_prenom');
            
            // Supprimer les colonnes nom et prenom
            $table->dropColumn(['nom', 'prenom']);
        });
        
        \Log::info('Migration users: Colonnes nom et prenom supprimées (rollback)');
    }
};