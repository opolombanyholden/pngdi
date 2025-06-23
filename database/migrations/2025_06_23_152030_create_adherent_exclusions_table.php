<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherent_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adherent_id')->constrained()->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained();
            $table->enum('type_exclusion', [
                'demission_volontaire',
                'exclusion_disciplinaire',
                'non_paiement_cotisation',
                'incompatibilite',
                'deces',
                'autre'
            ]);
            $table->text('motif_detaille');
            $table->date('date_decision');
            $table->string('numero_decision')->nullable();
            $table->string('document_decision')->comment('Scan de la décision');
            $table->string('lettre_notification')->nullable();
            $table->boolean('conteste')->default(false);
            $table->text('detail_contestation')->nullable();
            $table->foreignId('validated_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['adherent_id', 'organisation_id']);
            $table->index('type_exclusion');
            $table->index('date_decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherent_exclusions');
    }
};