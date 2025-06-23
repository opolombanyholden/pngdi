<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherent_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->foreignId('imported_by')->constrained('users');
            $table->string('fichier_original');
            $table->string('fichier_traite')->nullable();
            $table->integer('total_lignes');
            $table->integer('lignes_importees')->default(0);
            $table->integer('lignes_rejetees')->default(0);
            $table->integer('doublons_detectes')->default(0);
            $table->enum('statut', ['en_cours', 'complete', 'echoue', 'partiel'])->default('en_cours');
            $table->json('erreurs')->nullable()->comment('Détail des erreurs par ligne');
            $table->json('doublons')->nullable()->comment('Liste des NIP en doublon');
            $table->json('statistiques')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['organisation_id', 'statut']);
            $table->index('imported_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherent_imports');
    }
};