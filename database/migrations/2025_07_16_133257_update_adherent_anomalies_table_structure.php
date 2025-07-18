<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdherentAnomaliesTableStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adherent_anomalies', function (Blueprint $table) {
            // Vérifier et ajouter les colonnes manquantes
            
            if (!Schema::hasColumn('adherent_anomalies', 'detectee_le')) {
                $table->timestamp('detectee_le')->nullable()->after('message_anomalie');
            }
            
            if (!Schema::hasColumn('adherent_anomalies', 'date_correction')) {
                $table->timestamp('date_correction')->nullable()->after('detectee_le');
            }
            
            if (!Schema::hasColumn('adherent_anomalies', 'commentaire_correction')) {
                $table->text('commentaire_correction')->nullable()->after('date_correction');
            }
            
            if (!Schema::hasColumn('adherent_anomalies', 'description')) {
                $table->text('description')->nullable()->after('commentaire_correction');
            }
            
            if (!Schema::hasColumn('adherent_anomalies', 'valeur_incorrecte')) {
                $table->json('valeur_incorrecte')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('adherent_anomalies', 'impact_metier')) {
                $table->string('impact_metier')->nullable()->after('valeur_incorrecte');
            }
            
            if (!Schema::hasColumn('adherent_anomalies', 'priorite')) {
                $table->integer('priorite')->default(3)->after('impact_metier');
            }
            
            // Modifier le type_anomalie pour inclure 'critique'
            $table->enum('type_anomalie', ['critique', 'majeure', 'mineure'])->change();
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
                'detectee_le',
                'date_correction', 
                'commentaire_correction',
                'description',
                'valeur_incorrecte',
                'impact_metier',
                'priorite'
            ]);
            
            // Remettre l'enum original
            $table->enum('type_anomalie', ['majeure', 'mineure'])->change();
        });
    }
}