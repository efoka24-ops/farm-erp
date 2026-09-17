<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mises_bas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('saillie_id');
            $table->date('date_mise_bas');
            $table->unsignedTinyInteger('nombre_nes');
            $table->unsignedTinyInteger('nombre_survivants');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('saillie_id')->references('id')->on('saillies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mises_bas');
    }
};
