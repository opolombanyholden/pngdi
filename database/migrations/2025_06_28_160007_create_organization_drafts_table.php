<?php
// ========================================================================
// FICHIER: database/migrations/2025_01_XX_XXXXXX_create_organization_drafts_table.php
// Migration pour les brouillons d'organisations
// ========================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationDraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_drafts', function (Blueprint $table) {
            $table->id();
            
            // Référence utilisateur
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Type d'organisation (peut être null si pas encore sélectionné)
            $table->enum('organization_type', [
                'association', 
                'ong', 
                'parti_politique', 
                'confession_religieuse'
            ])->nullable();
            
            // Données du formulaire en JSON
            $table->json('form_data');
            
            // Étape actuelle du formulaire
            $table->integer('current_step')->default(1);
            
            // Métadonnées additionnelles
            $table->integer('completion_percentage')->default(0);
            $table->json('validation_errors')->nullable();
            $table->string('session_id')->nullable();
            
            // Timestamps personnalisés
            $table->timestamp('last_saved_at');
            $table->timestamp('expires_at')->nullable(); // Expiration du brouillon
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['user_id', 'organization_type']);
            $table->index('last_saved_at');
            $table->index('expires_at');
            $table->index('current_step');
            
            // Contrainte unique : un seul brouillon actif par utilisateur et type
            $table->unique(['user_id', 'organization_type'], 'unique_user_org_type_draft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_drafts');
    }
}