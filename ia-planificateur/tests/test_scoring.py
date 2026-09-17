"""T065 : tests unitaires calcul marge journalière. T067 : performance."""

from __future__ import annotations

import time

from src.model.scoring import AnimalAEvaluer, classer, marge_journaliere, noter_animal


def _animal(**kwargs) -> AnimalAEvaluer:
    defaut = dict(
        id="a1", espece="bovin", poids_actuel_kg=300, cout_total=100_000,
        age_jours=200, prix_marche_kg=1000,
    )
    defaut.update(kwargs)
    return AnimalAEvaluer(**defaut)


def test_marge_journaliere_positive():
    # 300kg * 1000 RWF/kg = 300 000 CA estimé ; coût 100 000 ; 200 jours
    animal = _animal()
    assert marge_journaliere(animal) == 1000.0  # (300000 - 100000) / 200


def test_marge_journaliere_negative_si_cout_superieur_au_ca():
    animal = _animal(cout_total=400_000)
    assert marge_journaliere(animal) < 0


def test_marge_journaliere_nulle_si_age_zero():
    animal = _animal(age_jours=0)
    assert marge_journaliere(animal) == 0.0


def test_noter_animal_retourne_prix_estime_et_ca_potentiel():
    animal = _animal(poids_actuel_kg=350, prix_marche_kg=1200, cout_total=150_000)
    reco = noter_animal(animal)

    assert reco.prix_estime == 350 * 1200
    assert reco.ca_potentiel == 350 * 1200 - 150_000


def test_classer_trie_par_score_decroissant():
    bon_candidat = _animal(id="bon", cout_total=50_000, age_jours=150)
    mauvais_candidat = _animal(id="mauvais", cout_total=500_000, age_jours=400)

    resultats = classer([mauvais_candidat, bon_candidat])

    assert resultats[0].id == "bon"
    assert resultats[0].score > resultats[1].score


def test_performance_classement_sous_5_secondes():
    """T067 : recommandation en < 5s même sur un cheptel de grande taille."""
    animaux = [_animal(id=f"a{i}", age_jours=100 + i) for i in range(2000)]

    debut = time.perf_counter()
    classer(animaux)
    duree = time.perf_counter() - debut

    assert duree < 5.0, f"Classement trop lent : {duree}s pour {len(animaux)} animaux"
