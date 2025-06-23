<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained();
            $table->foreignId('archived_by')->constrained('users');
            $table->text('motif_archivage');
            $table->json('snapshot_data');
            $table->timestamp('archived_at');
            $table->timestamps();
            
            $table->index('dossier_id');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_archives');
    }
};