<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_entities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('type', [
                'direction', 
                'service', 
                'departement', 
                'commission',
                'externe'
            ]);
            $table->string('email_notification')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('capacite_traitement')->default(10)->comment('Nombre de dossiers par jour');
            $table->json('horaires_travail')->nullable()->comment('Horaires de travail');
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_entities');
    }
};