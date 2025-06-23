<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->foreignId('locked_by')->constrained('users');
            $table->foreignId('workflow_step_id')->constrained();
            $table->string('session_id')->comment('ID de session pour éviter les conflits');
            $table->timestamp('locked_at')->useCurrent(); // ✅ Utilise la date actuelle par défaut
            $table->timestamp('expires_at')->nullable(); // ✅ Nullable
            $table->boolean('is_active')->default(true);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->unique(['dossier_id', 'is_active']);
            $table->index(['locked_by', 'is_active']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_locks');
    }
};