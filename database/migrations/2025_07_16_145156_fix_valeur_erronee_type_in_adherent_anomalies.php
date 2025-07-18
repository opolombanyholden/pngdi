<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixValeurErroneeTypeInAdherentAnomalies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modifier le type de valeur_erronee de TEXT vers JSON pour compatibilité avec le modèle
        DB::statement("ALTER TABLE adherent_anomalies MODIFY COLUMN valeur_erronee JSON NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remettre en TEXT
        DB::statement("ALTER TABLE adherent_anomalies MODIFY COLUMN valeur_erronee TEXT NULL");
    }
}