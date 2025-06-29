<?php
/**
 * MIGRATION 2/5 - Création Table Roles
 * Table pour gérer les rôles système PNGDI
 * Compatible PHP 7.3.29 - Laravel
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Nom unique du rôle');
            $table->string('display_name', 100)->comment('Nom affiché du rôle');
            $table->text('description')->nullable()->comment('Description du rôle');
            $table->string('color', 7)->default('#007bff')->comment('Couleur hex pour UI');
            $table->integer('level')->default(1)->comment('Niveau hiérarchique');
            $table->boolean('is_active')->default(true)->comment('Rôle actif ou non');
            $table->timestamps();
            
            // Index pour optimisation des requêtes
            $table->index(['is_active', 'level']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}