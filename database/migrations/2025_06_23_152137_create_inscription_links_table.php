<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscription_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->string('token')->unique();
            $table->string('url_courte')->unique()->nullable();
            $table->string('nom_campagne');
            $table->text('description')->nullable();
            $table->integer('limite_inscriptions')->nullable();
            $table->integer('inscriptions_actuelles')->default(0);
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->boolean('requiert_validation')->default(true);
            $table->json('champs_supplementaires')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['organisation_id', 'is_active']);
            $table->index('token');
            $table->index(['date_debut', 'date_fin']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription_links');
    }
};