<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table append-only : aucune route/policy applicative ne doit exposer d'UPDATE/DELETE
        // sur ce journal (principe constitutionnel IV — traçabilité).
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('exploitation_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->string('auditable_id')->nullable();
            $table->json('donnees_avant')->nullable();
            $table->json('donnees_apres')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['exploitation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
