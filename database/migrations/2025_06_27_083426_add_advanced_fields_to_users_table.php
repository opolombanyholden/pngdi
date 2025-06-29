<?php
/**
 * MIGRATION FINALE CORRIGÉE - Extension Table Users PNGDI
 * Ajout seulement des colonnes manquantes basé sur l'analyse de la structure existante
 * Compatible PHP 7.3.29 - Laravel
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // COLONNES À AJOUTER (qui n'existent pas dans la structure actuelle)
            
            // 1. Système de rôles PNGDI (remplace le enum 'role' existant)
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('password');
            }
            
            // 2. Statut utilisateur étendu
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])
                      ->default('pending')->after('role_id');
            }
            
            // 3. Tentatives de connexion (différent de failed_login_attempts)
            if (!Schema::hasColumn('users', 'login_attempts')) {
                $table->integer('login_attempts')->default(0)->after('last_login_at');
            }
            
            // 4. Vérification utilisateur
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('login_attempts');
            }
            
            // 5. Token de vérification
            if (!Schema::hasColumn('users', 'verification_token')) {
                $table->string('verification_token')->nullable()->after('is_verified');
            }
            
            // 6. Avatar (différent de photo_path)
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('photo_path');
            }
            
            // 7. Audit trail - créateur
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            }
            
            // 8. Audit trail - modificateur
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
        
        // Ajouter les index seulement s'ils n'existent pas
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->index(['role_id', 'status'], 'users_role_id_status_index');
            } catch (Exception $e) {
                // Index existe déjà ou erreur, on continue
            }
            
            try {
                $table->index('verification_token', 'users_verification_token_index');
            } catch (Exception $e) {
                // Index existe déjà ou erreur, on continue
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les index d'abord
            try {
                $table->dropIndex('users_role_id_status_index');
            } catch (Exception $e) {
                // Index n'existe pas, on continue
            }
            
            try {
                $table->dropIndex('users_verification_token_index');
            } catch (Exception $e) {
                // Index n'existe pas, on continue
            }
            
            // Supprimer seulement les colonnes ajoutées par cette migration
            $columnsToRemove = [];
            
            if (Schema::hasColumn('users', 'role_id')) {
                $columnsToRemove[] = 'role_id';
            }
            if (Schema::hasColumn('users', 'status')) {
                $columnsToRemove[] = 'status';
            }
            if (Schema::hasColumn('users', 'login_attempts')) {
                $columnsToRemove[] = 'login_attempts';
            }
            if (Schema::hasColumn('users', 'is_verified')) {
                $columnsToRemove[] = 'is_verified';
            }
            if (Schema::hasColumn('users', 'verification_token')) {
                $columnsToRemove[] = 'verification_token';
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $columnsToRemove[] = 'avatar';
            }
            if (Schema::hasColumn('users', 'created_by')) {
                $columnsToRemove[] = 'created_by';
            }
            if (Schema::hasColumn('users', 'updated_by')) {
                $columnsToRemove[] = 'updated_by';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
}