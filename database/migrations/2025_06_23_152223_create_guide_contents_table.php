<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_contents', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type_operation', [
                'creation', 'modification', 'cessation',
                'ajout_adherent', 'retrait_adherent',
                'declaration_activite', 'changement_statutaire'
            ]);
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse'])->nullable();
            $table->string('titre');
            $table->text('contenu_intro');
            $table->json('etapes');
            $table->json('documents_requis');
            $table->json('liens_utiles')->nullable();
            $table->string('video_tutoriel')->nullable();
            $table->integer('temps_estime')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type_operation', 'type_organisation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_contents');
    }
};