<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions_alimentation', function (Blueprint $table) {
            $table->uuid('categorie_stock_id')->nullable()->after('animal_id')
                ->comment('Déduction auto du stock si renseigné (T059) ; sinon simple enregistrement (compat US1)');
            $table->foreign('categorie_stock_id')->references('id')->on('categories_stock')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('distributions_alimentation', function (Blueprint $table) {
            $table->dropForeign(['categorie_stock_id']);
            $table->dropColumn('categorie_stock_id');
        });
    }
};
