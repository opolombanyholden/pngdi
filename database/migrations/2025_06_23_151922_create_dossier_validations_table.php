<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->foreignId('workflow_step_id')->constrained();
            $table->foreignId('validation_entity_id')->constrained();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->enum('decision', ['approuve', 'rejete', 'en_attente'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->string('visa')->nullable();
            $table->string('reference')->nullable();
            $table->string('numero_enregistrement')->nullable();
            $table->string('document_genere')->nullable()->comment('Chemin du document généré');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->integer('duree_traitement')->nullable()->comment('Durée en minutes');
            $table->json('donnees_supplementaires')->nullable();
            $table->timestamps();
            
            $table->index(['dossier_id', 'workflow_step_id']);
            $table->index(['validation_entity_id', 'decision']);
            $table->index('validated_by');
            $table->index('decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_validations');
    }
};