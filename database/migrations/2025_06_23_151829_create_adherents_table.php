<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('nip')->comment('Numéro d\'Identification Personnel');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->string('nationalite')->default('Gabonaise');
            
            // Contacts
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            
            // Géolocalisation
            $table->string('adresse_complete')->nullable();
            $table->string('province')->nullable();
            $table->string('departement')->nullable();
            $table->string('canton')->nullable();
            $table->string('prefecture')->nullable();
            $table->string('sous_prefecture')->nullable();
            $table->string('regroupement')->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->nullable();
            $table->string('ville_commune')->nullable();
            $table->string('arrondissement')->nullable();
            $table->string('quartier')->nullable();
            $table->string('village')->nullable();
            $table->string('lieu_dit')->nullable();
            
            // Documents
            $table->string('photo')->nullable();
            $table->string('piece_identite')->nullable()->comment('Scan de la pièce');
            
            // Statut
            $table->date('date_adhesion');
            $table->date('date_exclusion')->nullable();
            $table->text('motif_exclusion')->nullable();
            $table->boolean('is_fondateur')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('fondateur_id')->nullable()->constrained('fondateurs')
                  ->comment('Lien vers la table fondateurs si c\'est un fondateur');
            
            // Historique
            $table->json('historique')->nullable()->comment('Historique des modifications');
            
            $table->timestamps();
            
            // Index unique pour garantir l'unicité dans les partis politiques
            $table->unique(['nip', 'organisation_id']);
            $table->index('nip');
            $table->index(['organisation_id', 'is_active']);
            $table->index('fondateur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherents');
    }
};