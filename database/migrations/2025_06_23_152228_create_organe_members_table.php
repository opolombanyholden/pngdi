<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organe_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->foreignId('organe_type_id')->constrained();
            $table->string('nip');
            $table->string('nom');
            $table->string('prenom');
            $table->string('poste');
            $table->date('date_nomination');
            $table->date('date_fin_mandat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            
            $table->index(['organisation_id', 'organe_type_id']);
            $table->index('nip');
            
            // ✅ Nom court pour l'index unique
            $table->unique(['organisation_id', 'organe_type_id', 'poste', 'is_active'], 'unique_organe_member');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organe_members');
    }
};