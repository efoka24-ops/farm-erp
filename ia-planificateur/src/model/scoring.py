"""Score de recommandation de vente (T069).

État — ADAPTÉ : la spec demande un modèle Random Forest fine-tuné sur des
données RAB Rwanda (3 ans) + ILRI. Aucune des deux sources n'est
accessible (pas de credential RAB, pas de jeu de données ILRI obtenu) : un
vrai entraînement produirait un modèle non validé, potentiellement pire
qu'une heuristique transparente. On utilise donc un score composite
explicite et documenté, remplaçable par un vrai modèle entraîné dès qu'un
jeu de données réel est disponible (le contrat de `noter_animal` ci-dessous
resterait inchangé : mêmes entrées, même sortie).

Score = pondération de :
  - marge journalière normalisée (poids dominant : c'est le critère
    économique central demandé par la spec)
  - proximité du poids de l'animal par rapport au poids de vente optimal
    pour l'espèce (évite de recommander un animal encore trop jeune/léger)
"""

from __future__ import annotations

from dataclasses import dataclass

# Poids de vente optimal indicatif par espèce (kg) — valeurs usuelles
# d'élevage, à affiner avec des données réelles RAB/ILRI si disponibles.
POIDS_OPTIMAL_KG = {
    "bovin": 350,
    "caprin": 30,
    "ovin": 35,
    "porcin": 90,
    "camelin": 400,
}


@dataclass
class AnimalAEvaluer:
    id: str
    espece: str
    poids_actuel_kg: float
    cout_total: float
    age_jours: int
    prix_marche_kg: float


@dataclass
class Recommandation:
    id: str
    marge_journaliere: float
    prix_estime: float
    ca_potentiel: float
    score: float


def marge_journaliere(animal: AnimalAEvaluer) -> float:
    """T065 : (chiffre d'affaires estimé - coût total) / âge en jours."""
    prix_estime = animal.poids_actuel_kg * animal.prix_marche_kg
    if animal.age_jours <= 0:
        return 0.0
    return round((prix_estime - animal.cout_total) / animal.age_jours, 2)


def noter_animal(animal: AnimalAEvaluer) -> Recommandation:
    prix_estime = round(animal.poids_actuel_kg * animal.prix_marche_kg, 2)
    ca_potentiel = round(prix_estime - animal.cout_total, 2)
    marge = marge_journaliere(animal)

    poids_optimal = POIDS_OPTIMAL_KG.get(animal.espece, animal.poids_actuel_kg or 1)
    proximite_poids = 1 - min(abs(animal.poids_actuel_kg - poids_optimal) / poids_optimal, 1)

    # Marge journalière dominante (70%), proximité du poids optimal (30%).
    # La marge peut être négative (vente à perte) : on la borne à 0 dans le
    # score sans jamais masquer la valeur réelle retournée à l'appelant.
    score = round(max(marge, 0) * 0.7 + proximite_poids * 100 * 0.3, 2)

    return Recommandation(
        id=animal.id,
        marge_journaliere=marge,
        prix_estime=prix_estime,
        ca_potentiel=ca_potentiel,
        score=score,
    )


def classer(animaux: list[AnimalAEvaluer]) -> list[Recommandation]:
    recommandations = [noter_animal(a) for a in animaux]
    return sorted(recommandations, key=lambda r: r.score, reverse=True)
