<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_entity_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['responsable', 'agent', 'superviseur'])->default('agent');
            $table->boolean('peut_valider')->default(true);
            $table->boolean('peut_rejeter')->default(true);
            $table->boolean('peut_assigner')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('charge_actuelle')->default(0)->comment('Nombre de dossiers en cours');
            $table->integer('capacite_max')->default(5)->comment('Nombre max de dossiers simultanés');
            $table->timestamps();
            
            $table->unique(['validation_entity_id', 'user_id']);
            $table->index(['is_active', 'charge_actuelle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_agents');
    }
};