<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->foreignId('declaration_type_id')->constrained();
            $table->string('numero_declaration')->unique();
            $table->string('titre');
            $table->text('description');
            $table->date('date_evenement')->nullable();
            $table->date('date_fin_evenement')->nullable();
            $table->string('lieu')->nullable();
            $table->integer('nombre_participants')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->enum('statut', ['brouillon', 'soumise', 'validee', 'rejetee', 'archivee'])
                  ->default('brouillon');
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->json('donnees_specifiques')->nullable();
            $table->timestamps();
            
            $table->index(['organisation_id', 'declaration_type_id']);
            $table->index(['statut', 'created_at']);
            $table->index('date_evenement');
            $table->index('numero_declaration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations');
    }
};