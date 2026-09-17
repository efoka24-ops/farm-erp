<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Valorisation FIFO : les sorties consomment les lots par ordre de
        // date de réception (le plus ancien d'abord). Le PMP (coût moyen
        // pondéré) peut être dérivé à tout moment en agrégeant les lots
        // actifs sans structure dédiée supplémentaire.
        Schema::create('lots_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('categorie_stock_id');
            $table->decimal('quantite_initiale', 10, 2);
            $table->decimal('quantite_restante', 10, 2);
            $table->decimal('cout_unitaire', 10, 2);
            $table->date('date_reception');
            $table->date('date_peremption')->nullable();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('categorie_stock_id')->references('id')->on('categories_stock')->cascadeOnDelete();
            $table->index(['categorie_stock_id', 'date_reception']);
            $table->index(['categorie_stock_id', 'date_peremption']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots_stock');
    }
};
