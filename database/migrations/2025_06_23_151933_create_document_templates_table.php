<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('type_document', [
                'recepisse_provisoire',
                'recepisse_definitif',
                'certificat_enregistrement',
                'attestation',
                'notification_rejet',
                'autre'
            ]);
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse'])->nullable();
            $table->text('template_content')->comment('Contenu HTML du template');
            $table->json('variables')->comment('Variables disponibles dans le template');
            $table->boolean('has_qr_code')->default(true);
            $table->boolean('has_watermark')->default(true);
            $table->boolean('has_signature')->default(true);
            $table->string('signature_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type_document', 'type_organisation']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};