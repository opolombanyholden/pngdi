<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnomaliesColumnsToAdherentsTable extends Migration
{
    public function up()
    {
        Schema::table('adherents', function (Blueprint $table) {
            
            if (!Schema::hasColumn('adherents', 'source')) {
                $table->string('source')->default('manuel')->after('anomalies_data');
            }
            
            if (!Schema::hasColumn('adherents', 'ligne_import')) {
                $table->integer('ligne_import')->nullable()->after('source');
            }
        });
    }

    public function down()
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'ligne_import'
            ]);
        });
    }
}