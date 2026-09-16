<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id');
            $table->decimal('poids_kg', 6, 2);
            $table->date('date_pesee');
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->index(['animal_id', 'date_pesee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesees');
    }
};
