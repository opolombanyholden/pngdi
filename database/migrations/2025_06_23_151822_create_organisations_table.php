<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('Opérateur créateur');
            $table->enum('type', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->string('nom')->unique();
            $table->string('sigle')->nullable();
            $table->text('objet')->comment('Objet social');
            
            // Adresse siège social avec géolocalisation complète
            $table->string('siege_social')->comment('Adresse complète');
            $table->string('province');
            $table->string('departement')->nullable();
            $table->string('canton')->nullable();
            $table->string('prefecture');
            $table->string('sous_prefecture')->nullable();
            $table->string('regroupement')->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->default('urbaine');
            $table->string('ville_commune')->nullable()->comment('Pour zone urbaine');
            $table->string('arrondissement')->nullable()->comment('Pour zone urbaine');
            $table->string('quartier')->nullable();
            $table->string('village')->nullable()->comment('Pour zone rurale');
            $table->string('lieu_dit')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Contacts
            $table->string('email')->nullable();
            $table->string('telephone');
            $table->string('telephone_secondaire')->nullable();
            $table->string('site_web')->nullable();
            
            // Informations administratives
            $table->string('numero_recepisse')->nullable()->unique();
            $table->date('date_creation');
            $table->enum('statut', ['brouillon', 'soumis', 'en_validation', 'approuve', 'rejete', 'suspendu', 'radie'])
                  ->default('brouillon');
            $table->boolean('is_active')->default(true);
            $table->integer('nombre_adherents_min')->default(10);
            
            // Organes de gestion (stockage JSON)
            $table->json('organes_gestion')->nullable()->comment('Membres des organes de gestion avec NIP');
            
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'type']);
            $table->index('statut');
            $table->index('numero_recepisse');
            $table->index(['province', 'prefecture']);
            $table->index('zone_type');
            
            // Contrainte unique sur sigle seulement s'il n'est pas null
            $table->unique('sigle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};