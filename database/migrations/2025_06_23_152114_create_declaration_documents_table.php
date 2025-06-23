<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declaration_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')->constrained()->onDelete('cascade');
            $table->string('type_document');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('type_mime');
            $table->integer('taille');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('declaration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaration_documents');
    }
};