<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animaux', function (Blueprint $table) {
            $table->uuid('mise_bas_id')->nullable()->after('pere_id');
            $table->foreign('mise_bas_id')->references('id')->on('mises_bas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('animaux', function (Blueprint $table) {
            $table->dropForeign(['mise_bas_id']);
            $table->dropColumn('mise_bas_id');
        });
    }
};
