<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->json('geolocation')->nullable();
            $table->boolean('verification_reussie');
            $table->text('motif_echec')->nullable();
            $table->timestamps();
            
            $table->index(['qr_code_id', 'created_at']);
            $table->index('verification_reussie');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};