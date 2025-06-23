<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declaration_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->enum('categorie', [
                'activite',
                'evenement',
                'publication',
                'changement_statutaire',
                'changement_bureau',
                'rapport_annuel',
                'autre'
            ]);
            $table->json('types_organisation')->comment('Types d\'organisation concernés');
            $table->boolean('is_periodique')->default(false);
            $table->enum('periodicite', ['mensuelle', 'trimestrielle', 'semestrielle', 'annuelle'])->nullable();
            $table->integer('delai_declaration')->nullable()->comment('Délai en jours après l\'événement');
            $table->json('documents_requis')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['categorie', 'is_active']);
            $table->index('is_periodique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaration_types');
    }
};