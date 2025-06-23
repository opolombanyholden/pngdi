<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('type')->comment('Siège, antenne, bureau, etc.');
            
            // Géolocalisation complète
            $table->string('adresse');
            $table->string('province');
            $table->string('departement')->nullable();
            $table->string('canton')->nullable();
            $table->string('prefecture');
            $table->string('sous_prefecture')->nullable();
            $table->string('regroupement')->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->default('urbaine');
            $table->string('ville_commune')->nullable();
            $table->string('arrondissement')->nullable();
            $table->string('quartier')->nullable();
            $table->string('village')->nullable();
            $table->string('lieu_dit')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Contacts
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_telephone')->nullable();
            $table->string('responsable_email')->nullable();
            
            // Statut
            $table->boolean('is_siege_principal')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Index
            $table->index('organisation_id');
            $table->index(['latitude', 'longitude']);
            $table->index(['province', 'prefecture']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};