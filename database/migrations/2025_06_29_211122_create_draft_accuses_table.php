<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftAccusesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('draft_accuses', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('draft_id')->constrained('organization_drafts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Informations de l'accusé
            $table->integer('step_number');
            $table->string('step_name');
            $table->string('accuse_type')->default('step_completion'); // step_completion, validation, etc.
            $table->string('numero_accuse')->unique();
            
            // Contenu
            $table->text('contenu_html')->nullable();
            $table->string('fichier_pdf')->nullable();
            $table->json('donnees_etape')->nullable();
            
            // Métadonnées
            $table->string('hash_verification');
            $table->string('qr_code')->nullable();
            $table->boolean('is_valide')->default(true);
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            
            // Audit
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['draft_id', 'step_number']);
            $table->index('numero_accuse');
            $table->index(['user_id', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_accuses');
    }
}