<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('exploitation_id')->nullable()->after('id');
            $table->foreignId('role_id')->nullable()->after('exploitation_id')->constrained('roles')->nullOnDelete();
            $table->string('pin_hash')->nullable()->after('password');
            $table->unsignedTinyInteger('pin_attempts')->default(0)->after('pin_hash');
            $table->timestamp('pin_locked_until')->nullable()->after('pin_attempts');
            $table->boolean('two_factor_enabled')->default(true)->after('pin_locked_until');
            $table->boolean('actif')->default(true)->after('two_factor_enabled');

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['exploitation_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'exploitation_id', 'role_id', 'pin_hash', 'pin_attempts',
                'pin_locked_until', 'two_factor_enabled', 'actif',
            ]);
        });
    }
};
