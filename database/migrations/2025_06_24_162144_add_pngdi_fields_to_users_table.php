<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Vérifier et ajouter seulement les colonnes manquantes
            $columns = Schema::getColumnListing('users');
            
            if (!in_array('nip', $columns)) {
                $table->string('nip')->nullable()->unique()->after('email')
                    ->comment('Numéro d\'Identification Personnel');
            }
            
            if (!in_array('date_naissance', $columns)) {
                $table->date('date_naissance')->nullable()->after('country');
            }
            
            if (!in_array('lieu_naissance', $columns)) {
                $table->string('lieu_naissance')->nullable()->after('date_naissance');
            }
            
            if (!in_array('sexe', $columns)) {
                $table->enum('sexe', ['M', 'F'])->nullable()->after('lieu_naissance');
            }
            
            if (!in_array('photo_path', $columns)) {
                $table->string('photo_path')->nullable()->after('sexe');
            }
            
            if (!in_array('locked_until', $columns)) {
                $table->timestamp('locked_until')->nullable()->after('two_factor_secret');
            }
            
            if (!in_array('failed_login_attempts', $columns)) {
                $table->integer('failed_login_attempts')->default(0)->after('locked_until');
            }
            
            if (!in_array('preferences', $columns)) {
                $table->json('preferences')->nullable()->after('last_login_ip')
                    ->comment('Préférences utilisateur');
            }
            
            if (!in_array('metadata', $columns)) {
                $table->json('metadata')->nullable()->after('preferences')
                    ->comment('Métadonnées additionnelles');
            }
        });

        // Ajouter les index seulement s'ils n'existent pas
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('users');
        
        Schema::table('users', function (Blueprint $table) use ($indexes) {
            if (!array_key_exists('users_role_index', $indexes)) {
                $table->index('role');
            }
            
            if (!array_key_exists('users_is_active_index', $indexes)) {
                $table->index('is_active');
            }
            
            if (!array_key_exists('users_role_is_active_index', $indexes)) {
                $table->index(['role', 'is_active']);
            }
            
            if (!array_key_exists('users_locked_until_index', $indexes)) {
                $table->index('locked_until');
            }
            
            if (!array_key_exists('users_email_is_active_index', $indexes)) {
                $table->index(['email', 'is_active']);
            }
        });

        // Mettre à jour les préférences par défaut
        DB::table('users')->whereNull('preferences')->update([
            'preferences' => json_encode([
                'notifications' => [
                    'email' => true,
                    'sms' => false,
                    'browser' => true
                ],
                'language' => 'fr',
                'timezone' => 'Africa/Libreville',
                'theme' => 'light'
            ])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('users');
        
        Schema::table('users', function (Blueprint $table) use ($indexes) {
            if (array_key_exists('users_role_index', $indexes)) {
                $table->dropIndex('users_role_index');
            }
            if (array_key_exists('users_is_active_index', $indexes)) {
                $table->dropIndex('users_is_active_index');
            }
            if (array_key_exists('users_role_is_active_index', $indexes)) {
                $table->dropIndex('users_role_is_active_index');
            }
            if (array_key_exists('users_locked_until_index', $indexes)) {
                $table->dropIndex('users_locked_until_index');
            }
            if (array_key_exists('users_email_is_active_index', $indexes)) {
                $table->dropIndex('users_email_is_active_index');
            }
        });
        
        // Supprimer les colonnes
        Schema::table('users', function (Blueprint $table) {
            $columns = Schema::getColumnListing('users');
            
            $columnsToRemove = [];
            
            if (in_array('nip', $columns)) {
                $columnsToRemove[] = 'nip';
            }
            if (in_array('date_naissance', $columns)) {
                $columnsToRemove[] = 'date_naissance';
            }
            if (in_array('lieu_naissance', $columns)) {
                $columnsToRemove[] = 'lieu_naissance';
            }
            if (in_array('sexe', $columns)) {
                $columnsToRemove[] = 'sexe';
            }
            if (in_array('photo_path', $columns)) {
                $columnsToRemove[] = 'photo_path';
            }
            if (in_array('preferences', $columns)) {
                $columnsToRemove[] = 'preferences';
            }
            if (in_array('metadata', $columns)) {
                $columnsToRemove[] = 'metadata';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};