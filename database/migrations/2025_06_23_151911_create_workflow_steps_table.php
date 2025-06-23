<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->enum('type_operation', [
                'creation', 
                'modification', 
                'cessation', 
                'ajout_adherent', 
                'retrait_adherent',
                'declaration_activite',
                'changement_statutaire'
            ]);
            $table->integer('numero_passage')->comment('Ordre de passage dans le workflow');
            $table->boolean('is_active')->default(true);
            $table->boolean('permet_rejet')->default(true);
            $table->boolean('permet_commentaire')->default(true);
            $table->boolean('genere_document')->default(false);
            $table->string('template_document')->nullable()->comment('Template du document à générer');
            $table->json('champs_requis')->nullable()->comment('Champs à remplir à cette étape');
            $table->integer('delai_traitement')->default(48)->comment('Délai en heures');
            $table->timestamps();
            
            $table->index(['type_organisation', 'type_operation', 'is_active']);
            $table->index('numero_passage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};