<?php
/**
 * MIGRATION 4/5 - Création Table Pivot Role-Permissions
 * Table de liaison many-to-many entre roles et permissions
 * Compatible PHP 7.3.29 - Laravel
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->comment('ID du rôle');
            $table->unsignedBigInteger('permission_id')->comment('ID de la permission');
            $table->timestamps();
            
            // Clé primaire composite pour éviter les doublons
            $table->primary(['role_id', 'permission_id']);
            
            // Clés étrangères avec suppression en cascade
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // Index pour optimiser les requêtes
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
}