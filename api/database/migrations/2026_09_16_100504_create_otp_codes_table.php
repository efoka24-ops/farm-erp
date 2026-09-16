<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('expire_at');
            $table->timestamp('consomme_at')->nullable();
            $table->unsignedTinyInteger('tentatives')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'expire_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
