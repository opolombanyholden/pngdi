<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNipDatabaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nip_database', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 255)->comment('Nom de famille');
            $table->string('prenom', 255)->comment('Prénom');
            $table->date('date_naissance')->comment('Date de naissance extraite du NIP');
            $table->string('lieu_naissance', 255)->nullable()->comment('Lieu de naissance');
            $table->string('nip', 20)->unique()->comment('Numéro d\'Identification Personnel unique');
            $table->enum('sexe', ['M', 'F'])->comment('Sexe extrait du NIP');
            $table->enum('statut', ['actif', 'inactif', 'decede', 'suspendu'])->default('actif')->comment('Statut du NIP');
            $table->string('telephone', 20)->nullable()->comment('Numéro de téléphone');
            $table->string('email', 255)->nullable()->comment('Adresse email');
            $table->text('remarques')->nullable()->comment('Remarques ou notes administratives');
            
            // Métadonnées d'import
            $table->string('source_import', 100)->nullable()->comment('Source du fichier d\'import');
            $table->timestamp('date_import')->nullable()->comment('Date d\'import dans la base');
            $table->unsignedBigInteger('imported_by')->nullable()->comment('Utilisateur ayant effectué l\'import');
            $table->timestamp('last_verified_at')->nullable()->comment('Dernière vérification');
            
            // Audit
            $table->timestamps();
            
            // Index pour optimiser les performances (sans dupliquer la contrainte unique)
            $table->index(['nom', 'prenom']);
            $table->index(['date_naissance']);
            $table->index(['nip', 'statut']);
            $table->index(['statut']);
            $table->index(['date_import']);
            
            // Contrainte de clé étrangère
            $table->foreign('imported_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nip_database');
    }
}