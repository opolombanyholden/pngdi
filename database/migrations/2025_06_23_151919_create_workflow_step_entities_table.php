<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_step_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_step_id')->constrained()->onDelete('cascade');
            $table->foreignId('validation_entity_id')->constrained()->onDelete('cascade');
            $table->integer('ordre')->default(1)->comment('Ordre si plusieurs entités pour une étape');
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
            
            // ✅ Spécifier un nom court pour l'index unique
            $table->unique(['workflow_step_id', 'validation_entity_id'], 'unique_step_entity');
            $table->index('ordre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_step_entities');
    }
};