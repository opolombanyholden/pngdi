<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('workflow_step_id')->nullable()->constrained();
            $table->enum('type', ['interne', 'operateur', 'systeme'])->default('interne');
            $table->text('commentaire');
            $table->boolean('is_visible_operateur')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('dossier_comments');
            $table->json('fichiers_joints')->nullable();
            $table->timestamps();
            
            $table->index(['dossier_id', 'created_at']);
            $table->index(['type', 'is_visible_operateur']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_comments');
    }
};