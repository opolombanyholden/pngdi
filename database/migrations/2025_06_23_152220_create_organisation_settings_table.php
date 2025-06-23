<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->string('parametre');
            $table->string('valeur');
            $table->text('description')->nullable();
            $table->enum('type_valeur', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->boolean('is_editable')->default(true);
            $table->timestamps();
            
            $table->unique(['type_organisation', 'parametre']);
            $table->index('parametre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_settings');
    }
};