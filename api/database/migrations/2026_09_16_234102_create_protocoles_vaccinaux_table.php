<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Calendrier vaccinal automatique par espèce (T048) : protocoles simplifiés
        // inspirés des recommandations OIE Rwanda pour les espèces d'élevage
        // courantes. Référentiel global (pas par région) pour le MVP — le
        // raffinement par région nécessiterait une source officielle à intégrer.
        Schema::create('protocoles_vaccinaux', function (Blueprint $table) {
            $table->id();
            $table->string('espece');
            $table->string('vaccin');
            $table->unsignedSmallInteger('jour_apres_naissance');
            $table->boolean('rappel_annuel')->default(false);
            $table->timestamps();
        });

        DB::table('protocoles_vaccinaux')->insert([
            ['espece' => 'bovin', 'vaccin' => 'Fièvre aphteuse', 'jour_apres_naissance' => 90, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'bovin', 'vaccin' => 'Charbon symptomatique', 'jour_apres_naissance' => 120, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'bovin', 'vaccin' => 'Pasteurellose', 'jour_apres_naissance' => 60, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'caprin', 'vaccin' => 'Peste des petits ruminants (PPR)', 'jour_apres_naissance' => 90, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'caprin', 'vaccin' => 'Clostridiose', 'jour_apres_naissance' => 30, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'ovin', 'vaccin' => 'Peste des petits ruminants (PPR)', 'jour_apres_naissance' => 90, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'ovin', 'vaccin' => 'Clostridiose', 'jour_apres_naissance' => 30, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'porcin', 'vaccin' => 'Peste porcine classique', 'jour_apres_naissance' => 60, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
            ['espece' => 'camelin', 'vaccin' => 'Charbon bactéridien', 'jour_apres_naissance' => 120, 'rappel_annuel' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('protocoles_vaccinaux');
    }
};
