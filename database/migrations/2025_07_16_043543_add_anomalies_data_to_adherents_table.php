<?php

// ===============================================
// CRÉER: database/migrations/2025_07_16_000002_add_anomalies_data_to_adherents_table.php
// Commande: php artisan make:migration add_anomalies_data_to_adherents_table
// ===============================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnomaliesDataToAdherentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adherents', function (Blueprint $table) {
            // Vérifier si la colonne n'existe pas déjà
            if (!Schema::hasColumn('adherents', 'anomalies_data')) {
                $table->json('anomalies_data')
                      ->nullable()
                      ->after('anomalies_severity')
                      ->comment('Données détaillées des anomalies détectées au format JSON');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adherents', function (Blueprint $table) {
            if (Schema::hasColumn('adherents', 'anomalies_data')) {
                $table->dropColumn('anomalies_data');
            }
        });
    }
}