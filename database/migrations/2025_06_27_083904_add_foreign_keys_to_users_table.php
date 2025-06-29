<?php
/**
 * MIGRATION 6/6 - Ajout des Clés Étrangères à la Table Users
 * Création des relations après que toutes les tables existent
 * Compatible PHP 7.3.29 - Laravel
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Clé étrangère vers la table roles
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            // Clés étrangères pour audit trail (créateur)
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            // Clés étrangères pour audit trail (modificateur)
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
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
            // Supprimer les clés étrangères dans l'ordre inverse
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['role_id']);
        });
    }
}