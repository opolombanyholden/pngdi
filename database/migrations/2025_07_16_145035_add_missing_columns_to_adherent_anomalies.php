<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToAdherentAnomalies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adherent_anomalies', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            $table->text('description')->nullable()->after('commentaire_correction');
            $table->json('valeur_incorrecte')->nullable()->after('description');
            $table->string('impact_metier')->nullable()->after('valeur_incorrecte');
            $table->integer('priorite')->default(3)->after('impact_metier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adherent_anomalies', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'valeur_incorrecte', 
                'impact_metier',
                'priorite'
            ]);
        });
    }
}