<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('traitements', function (Blueprint $table) {
            $table->uuid('categorie_stock_id')->nullable()->after('medicament')
                ->comment('Déduction auto du stock de médicament si renseigné (T059)');
            $table->foreign('categorie_stock_id')->references('id')->on('categories_stock')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('traitements', function (Blueprint $table) {
            $table->dropForeign(['categorie_stock_id']);
            $table->dropColumn('categorie_stock_id');
        });
    }
};
