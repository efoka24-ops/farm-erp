<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('lot_stock_id');
            $table->enum('type', ['entree', 'sortie']);
            $table->decimal('quantite', 10, 2);
            $table->string('motif')->nullable();
            $table->string('lie_a_type')->nullable()->comment('Ex: DistributionAlimentation, Traitement');
            $table->string('lie_a_id')->nullable();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('lot_stock_id')->references('id')->on('lots_stock')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};
