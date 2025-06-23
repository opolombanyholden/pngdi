<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
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
            ])->default('creation');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(true);
            $table->integer('ordre')->default(0);
            $table->string('format_accepte')->default('pdf,jpg,png')->comment('Extensions acceptées');
            $table->integer('taille_max')->default(5)->comment('Taille max en MB');
            $table->timestamps();
            
            $table->index(['type_organisation', 'type_operation', 'is_active']);
            $table->index('ordre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};