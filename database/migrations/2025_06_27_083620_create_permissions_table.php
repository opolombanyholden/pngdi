<?php
/**
 * MIGRATION 3/5 - Création Table Permissions
 * Table pour gérer les permissions système PNGDI
 * Compatible PHP 7.3.29 - Laravel
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nom unique de la permission');
            $table->string('display_name', 150)->comment('Nom affiché de la permission');
            $table->string('category', 50)->comment('Catégorie: users, organizations, workflow, system, content');
            $table->text('description')->nullable()->comment('Description détaillée');
            $table->timestamps();
            
            // Index pour optimisation des requêtes
            $table->index('category');
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
        Schema::dropIfExists('permissions');
    }
}