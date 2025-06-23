<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organe_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->json('postes_disponibles');
            $table->integer('membres_min')->default(3);
            $table->integer('membres_max')->nullable();
            $table->boolean('is_obligatoire')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type_organisation', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organe_types');
    }
};