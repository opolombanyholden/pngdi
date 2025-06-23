<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->comment('Type de document');
            
            // ✅ Créer manuellement les colonnes morphs avec commentaires
            $table->string('verifiable_type');
            $table->unsignedBigInteger('verifiable_id');
            $table->index(['verifiable_type', 'verifiable_id']);
            
            $table->string('document_numero')->nullable();
            $table->json('donnees_verification')->comment('Données à afficher lors de la vérification');
            $table->string('hash_verification')->comment('Hash pour vérifier l\'intégrité');
            $table->integer('nombre_verifications')->default(0);
            $table->timestamp('derniere_verification')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('expire_at');
            $table->index('is_active');
        });
        
        // ✅ Ajouter le commentaire après la création de la table
        Schema::table('qr_codes', function (Blueprint $table) {
            DB::statement("ALTER TABLE `qr_codes` MODIFY `verifiable_type` VARCHAR(255) COMMENT 'Type du modèle vérifiable'");
            DB::statement("ALTER TABLE `qr_codes` MODIFY `verifiable_id` BIGINT UNSIGNED COMMENT 'ID du modèle vérifiable'");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};