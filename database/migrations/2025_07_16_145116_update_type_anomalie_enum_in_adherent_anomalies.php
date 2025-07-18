<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTypeAnomalieEnumInAdherentAnomalies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Utiliser une requête SQL brute pour modifier l'enum
        // car Laravel/Doctrine a des problèmes avec les types enum
        DB::statement("ALTER TABLE adherent_anomalies MODIFY COLUMN type_anomalie ENUM('critique', 'majeure', 'mineure') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remettre l'enum original (sans 'critique')
        DB::statement("ALTER TABLE adherent_anomalies MODIFY COLUMN type_anomalie ENUM('majeure', 'mineure') NOT NULL");
    }
}