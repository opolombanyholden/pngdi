<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajout des champs supplémentaires
            $table->enum('role', ['admin', 'agent', 'operator', 'visitor'])->default('visitor')->after('password');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Gabon');
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
        });
        
        // Ajout des index dans une transaction séparée
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Suppression des index
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            
            // Suppression des colonnes
            $table->dropColumn([
                'role', 'phone', 'address', 'city', 'country',
                'is_active', 'two_factor_enabled', 'two_factor_secret',
                'last_login_at', 'last_login_ip', 'failed_login_attempts',
                'locked_until'
            ]);
        });
    }
};