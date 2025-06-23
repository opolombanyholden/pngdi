<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained();
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('type_mime');
            $table->integer('taille')->comment('Taille en octets');
            $table->string('hash_fichier')->comment('Pour vérifier l\'intégrité');
            $table->boolean('is_validated')->default(false);
            $table->text('commentaire')->nullable();
            $table->timestamps();
            
            $table->index(['dossier_id', 'document_type_id']);
            $table->index('is_validated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};