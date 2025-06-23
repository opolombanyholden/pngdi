<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->enum('type_operation', [
                'creation',
                'soumission',
                'validation',
                'rejet',
                'modification',
                'retour_pour_correction',
                'archivage',
                'verrouillage',
                'deverrouillage',
                'assignation',
                'commentaire'
            ]);
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut')->nullable();
            $table->foreignId('workflow_step_id')->nullable()->constrained();
            $table->text('description');
            $table->json('donnees_avant')->nullable()->comment('Snapshot des données avant l\'opération');
            $table->json('donnees_apres')->nullable()->comment('Snapshot des données après l\'opération');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['dossier_id', 'created_at']);
            $table->index(['user_id', 'type_operation']);
            $table->index('type_operation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_operations');
    }
};