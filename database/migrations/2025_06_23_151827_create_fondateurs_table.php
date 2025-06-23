<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fondateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('nip')->comment('Numéro d\'Identification Personnel');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('sexe', ['M', 'F']);
            $table->string('nationalite')->default('Gabonaise');
            
            // Contacts
            $table->string('telephone');
            $table->string('telephone_secondaire')->nullable();
            $table->string('email')->nullable();
            
            // Géolocalisation complète
            $table->string('adresse_complete');
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
            
            // Documents
            $table->string('photo')->nullable();
            $table->string('piece_identite')->comment('Scan de la pièce d\'identité');
            $table->string('type_piece')->comment('CNI, Passeport, etc.');
            $table->string('numero_piece');
            
            // Rôle dans l'organisation
            $table->string('fonction')->comment('Président fondateur, Secrétaire général, etc.');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            
            $table->timestamps();
            
            // Index
            $table->index('organisation_id');
            $table->index('nip');
            $table->unique(['organisation_id', 'nip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fondateurs');
    }
};