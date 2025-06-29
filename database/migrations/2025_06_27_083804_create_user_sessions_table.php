<?php
/**
 * MIGRATION 5/5 - Création Table User Sessions
 * Table pour audit trail et gestion des sessions utilisateurs
 * Compatible PHP 7.3.29 - Laravel
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ID de l\'utilisateur');
            $table->string('session_id')->comment('ID de session Laravel');
            $table->string('ip_address', 45)->comment('Adresse IP (IPv4/IPv6)');
            $table->text('user_agent')->comment('Navigateur et OS');
            $table->timestamp('login_at')->comment('Date/heure de connexion');
            $table->timestamp('logout_at')->nullable()->comment('Date/heure de déconnexion');
            $table->boolean('is_active')->default(true)->comment('Session active ou fermée');
            $table->timestamps();
            
            // Index pour optimisation des requêtes
            $table->index(['user_id', 'is_active']);
            $table->index('session_id');
            $table->index('login_at');
            $table->index('ip_address');
            
            // Clé étrangère vers users
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
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
        Schema::dropIfExists('user_sessions');
    }
}