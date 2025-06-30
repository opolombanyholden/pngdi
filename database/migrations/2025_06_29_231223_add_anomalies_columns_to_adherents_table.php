<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            // Colonnes pour la gestion des anomalies
            $table->boolean('has_anomalies')->default(false)->after('is_active');
            $table->json('anomalies_data')->nullable()->after('has_anomalies');
            $table->enum('anomalies_severity', ['critique', 'majeure', 'mineure'])->nullable()->after('anomalies_data');
            
            // Index pour optimiser les requêtes
            $table->index(['has_anomalies', 'anomalies_severity']);
            $table->index(['is_active', 'has_anomalies']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->dropIndex(['has_anomalies', 'anomalies_severity']);
            $table->dropIndex(['is_active', 'has_anomalies']);
            
            $table->dropColumn([
                'has_anomalies',
                'anomalies_data', 
                'anomalies_severity'
            ]);
        });
    }
};