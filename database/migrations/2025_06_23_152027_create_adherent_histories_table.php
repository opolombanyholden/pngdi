<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherent_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adherent_id')->constrained()->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained();
            $table->enum('type_mouvement', [
                'adhesion',
                'exclusion',
                'demission',
                'transfert',
                'reintegration',
                'suspension',
                'deces',
                'radiation'
            ]);
            $table->foreignId('ancienne_organisation_id')->nullable()
                  ->constrained('organisations')->comment('Pour les transferts');
            $table->foreignId('nouvelle_organisation_id')->nullable()
                  ->constrained('organisations')->comment('Pour les transferts');
            $table->text('motif');
            $table->string('document_justificatif')->nullable();
            $table->date('date_effet');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('commentaire_validation')->nullable();
            $table->timestamps();
            
            $table->index(['adherent_id', 'type_mouvement']);
            $table->index(['organisation_id', 'date_effet']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherent_histories');
    }
};