from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

from .data.prix_marche import PrixMarcheService
from .model.scoring import AnimalAEvaluer, classer

app = FastAPI(title="TRU FARM ERP - IA Planificateur")

prix_marche_service = PrixMarcheService()


@app.get("/health")
def health():
    return {"status": "ok"}


class AnimalEntree(BaseModel):
    id: str
    espece: str
    poids_actuel_kg: float
    cout_total: float
    age_jours: int
    prix_marche_kg: float | None = None


class RecommandationsRequete(BaseModel):
    animaux: list[AnimalEntree]


@app.post("/recommandations-ventes")
def recommandations_ventes(requete: RecommandationsRequete):
    """T070 : score, prix estimé, CA potentiel par animal, triés du
    meilleur candidat à la vente au moins bon. Le prix marché par kg peut
    être fourni par l'appelant (déjà connu côté API) ou, à défaut, est
    résolu via le dernier prix connu localement (fallback T068).
    """
    animaux_a_evaluer: list[AnimalAEvaluer] = []

    for animal in requete.animaux:
        prix_kg = animal.prix_marche_kg

        if prix_kg is None:
            dernier = prix_marche_service.dernier_prix(animal.espece)
            if not dernier:
                raise HTTPException(
                    status_code=422,
                    detail=f"Aucun prix marché connu pour l'espèce '{animal.espece}' et aucun fourni.",
                )
            prix_kg = dernier["prix_kg"]

        animaux_a_evaluer.append(
            AnimalAEvaluer(
                id=animal.id,
                espece=animal.espece,
                poids_actuel_kg=animal.poids_actuel_kg,
                cout_total=animal.cout_total,
                age_jours=animal.age_jours,
                prix_marche_kg=prix_kg,
            )
        )

    recommandations = classer(animaux_a_evaluer)

    return {"recommandations": [r.__dict__ for r in recommandations]}


class PrixMarcheEntree(BaseModel):
    espece: str
    prix_kg: float


@app.post("/prix-marche")
def enregistrer_prix_marche(entree: PrixMarcheEntree):
    """Saisie manuelle en attendant l'intégration RAB réelle (T068)."""
    return prix_marche_service.enregistrer_prix(entree.espece, entree.prix_kg)


@app.get("/prix-marche/{espece}")
def obtenir_prix_marche(espece: str):
    dernier = prix_marche_service.dernier_prix(espece)

    if not dernier:
        raise HTTPException(status_code=404, detail=f"Aucun prix connu pour '{espece}'.")

    return dernier
