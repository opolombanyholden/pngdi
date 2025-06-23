<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->enum('type_operation', [
                'creation', 
                'modification', 
                'cessation', 
                'ajout_adherent', 
                'retrait_adherent',
                'declaration_activite',
                'changement_statutaire'
            ]);
            $table->string('numero_dossier')->unique();
            $table->enum('statut', ['brouillon', 'soumis', 'en_cours', 'approuve', 'rejete'])
                  ->default('brouillon');
            $table->foreignId('current_step_id')->nullable()
                  ->constrained('workflow_steps')->comment('Étape actuelle du workflow');
            $table->foreignId('assigned_to')->nullable()
                  ->constrained('users')->comment('Agent assigné');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users');
            $table->text('motif_rejet')->nullable();
            $table->json('donnees_supplementaires')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            
            $table->index(['organisation_id', 'statut']);
            $table->index(['statut', 'created_at']); // Pour FIFO
            $table->index('numero_dossier');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};