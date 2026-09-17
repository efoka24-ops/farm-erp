<?php

namespace App\Services\Stock;

use App\Models\CategorieStock;
use App\Models\LotStock;
use App\Models\MouvementStock;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Gestion des stocks (T058) : entrées par lot, sorties valorisées en FIFO
 * (les lots les plus anciens sont consommés en premier), alertes de seuil
 * de commande et de péremption.
 */
class StockService
{
    public function entrer(
        CategorieStock $categorie,
        float $quantite,
        float $coutUnitaire,
        \DateTimeInterface|string $dateReception,
        \DateTimeInterface|string|null $datePeremption = null,
    ): LotStock {
        $lot = LotStock::create([
            'exploitation_id' => $categorie->exploitation_id,
            'categorie_stock_id' => $categorie->id,
            'quantite_initiale' => $quantite,
            'quantite_restante' => $quantite,
            'cout_unitaire' => $coutUnitaire,
            'date_reception' => $dateReception,
            'date_peremption' => $datePeremption,
        ]);

        MouvementStock::create([
            'exploitation_id' => $categorie->exploitation_id,
            'lot_stock_id' => $lot->id,
            'type' => 'entree',
            'quantite' => $quantite,
        ]);

        return $lot;
    }

    /**
     * Sortie FIFO : consomme les lots non périmés du plus ancien au plus
     * récent. Lève une exception si le stock disponible est insuffisant
     * (mieux vaut bloquer que produire une quantité négative silencieuse).
     *
     * @return array{cout_total: float, lots_consommes: array}
     */
    public function sortir(CategorieStock $categorie, float $quantite, ?string $lieAType = null, ?string $lieAId = null): array
    {
        $lots = LotStock::where('categorie_stock_id', $categorie->id)
            ->where('quantite_restante', '>', 0)
            ->orderBy('date_reception')
            ->get();

        if ($lots->sum('quantite_restante') < $quantite) {
            throw new RuntimeException("Stock insuffisant pour '{$categorie->nom}' : demandé {$quantite}, disponible {$lots->sum('quantite_restante')}.");
        }

        $restantADeduire = $quantite;
        $coutTotal = 0.0;
        $lotsConsommes = [];

        foreach ($lots as $lot) {
            if ($restantADeduire <= 0) {
                break;
            }

            $pris = min((float) $lot->quantite_restante, $restantADeduire);
            $lot->decrement('quantite_restante', $pris);

            MouvementStock::create([
                'exploitation_id' => $categorie->exploitation_id,
                'lot_stock_id' => $lot->id,
                'type' => 'sortie',
                'quantite' => $pris,
                'lie_a_type' => $lieAType,
                'lie_a_id' => $lieAId,
            ]);

            $coutTotal += $pris * (float) $lot->cout_unitaire;
            $lotsConsommes[] = ['lot_id' => $lot->id, 'quantite' => $pris];
            $restantADeduire -= $pris;
        }

        return ['cout_total' => round($coutTotal, 2), 'lots_consommes' => $lotsConsommes];
    }

    public function niveauActuel(CategorieStock $categorie): float
    {
        return (float) $categorie->lots()->sum('quantite_restante');
    }

    /** Alerte seuil de commande (T056) : catégories sous leur seuil. */
    public function categoriesSousSeuil(string $exploitationId): Collection
    {
        return CategorieStock::where('exploitation_id', $exploitationId)
            ->get()
            ->filter(fn (CategorieStock $c) => $this->niveauActuel($c) <= (float) $c->seuil_alerte_quantite)
            ->values();
    }

    /** Alerte péremption (T057) : lots avec stock restant expirant sous N jours. */
    public function lotsPerissablesSous(string $exploitationId, int $joursSeuil = 30): Collection
    {
        return LotStock::where('exploitation_id', $exploitationId)
            ->where('quantite_restante', '>', 0)
            ->whereNotNull('date_peremption')
            ->where('date_peremption', '<=', now()->addDays($joursSeuil)->toDateString())
            ->with('categorie')
            ->orderBy('date_peremption')
            ->get();
    }
}
