<?php

/**
 * 📄 CONTENU POUR LE FICHIER : add_upload_fields_to_documents_table.php
 * 
 * LOCALISATION : database/migrations/YYYY_MM_DD_XXXXXX_add_upload_fields_to_documents_table.php
 * 
 * INSTRUCTIONS :
 * 1. Exécuter : php artisan make:migration add_upload_fields_to_documents_table --table=documents
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
        Schema::table('documents', function (Blueprint $table) {
            // Ajouter nom_original après nom_fichier
            $table->string('nom_original')->nullable()->after('nom_fichier')
                ->comment('Nom original du fichier uploadé par l\'utilisateur');
            
            // Ajouter uploaded_by après hash_fichier  
            $table->unsignedBigInteger('uploaded_by')->nullable()->after('hash_fichier')
                ->comment('ID de l\'utilisateur qui a uploadé le document');
                
            // Ajouter is_system_generated après is_validated
            $table->boolean('is_system_generated')->default(false)->after('is_validated')
                ->comment('Document généré automatiquement par le système (accusés, certificats, etc.)');
            
            // Clé étrangère pour uploaded_by vers la table users
            $table->foreign('uploaded_by', 'fk_documents_uploaded_by')
                ->references('id')->on('users')
                ->onDelete('set null')  // Si user supprimé, mettre NULL
                ->onUpdate('cascade');  // Si ID user modifié, suivre
                
            // Index pour optimiser les recherches
            $table->index('uploaded_by', 'idx_documents_uploaded_by');
            $table->index('is_system_generated', 'idx_documents_system_generated');
        });
        
        \Log::info('Migration documents: Colonnes upload metadata ajoutées avec succès');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Supprimer la clé étrangère d'abord
            $table->dropForeign('fk_documents_uploaded_by');
            
            // Supprimer les index
            $table->dropIndex('idx_documents_uploaded_by');
            $table->dropIndex('idx_documents_system_generated');
            
            // Supprimer les colonnes
            $table->dropColumn(['nom_original', 'uploaded_by', 'is_system_generated']);
        });
        
        \Log::info('Migration documents: Colonnes upload metadata supprimées (rollback)');
    }
};