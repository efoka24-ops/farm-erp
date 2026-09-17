"""Ingestion des prix marché RAB Rwanda (T068).

État : aucune credential/API RAB Rwanda n'a été fournie — l'ingestion
hebdomadaire automatique (vendredi 18h) n'est donc pas connectée à une
source externe réelle. Cette classe fournit :
  - un stockage local (JSON) des derniers prix connus, alimentable
    manuellement via `POST /prix-marche` en attendant l'intégration RAB ;
  - le mécanisme de fallback (dernier prix connu + date affichée) demandé
    par la spec, indépendamment de la source d'ingestion.
"""

from __future__ import annotations

import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Optional

FICHIER_PRIX = Path(__file__).parent / "prix_marche.json"


class PrixMarcheService:
    def __init__(self, fichier: Path = FICHIER_PRIX):
        self.fichier = fichier

    def _charger(self) -> dict:
        if not self.fichier.exists():
            return {}
        return json.loads(self.fichier.read_text(encoding="utf-8"))

    def _sauvegarder(self, donnees: dict) -> None:
        self.fichier.write_text(json.dumps(donnees, indent=2), encoding="utf-8")

    def enregistrer_prix(self, espece: str, prix_kg: float) -> dict:
        donnees = self._charger()
        entree = {
            "prix_kg": prix_kg,
            "date": datetime.now(timezone.utc).isoformat(),
        }
        donnees[espece] = entree
        self._sauvegarder(donnees)
        return entree

    def dernier_prix(self, espece: str) -> Optional[dict]:
        """Retourne {prix_kg, date, source} ou None si jamais renseigné.

        `source` vaut "connu" si un prix existe, jamais "rab_direct" tant
        que l'intégration RAB réelle n'est pas branchée — ce champ permet
        au client (API Laravel / écrans) d'afficher explicitement que le
        prix affiché peut être daté (fallback dernier prix connu).
        """
        donnees = self._charger()
        entree = donnees.get(espece)

        if not entree:
            return None

        return {**entree, "source": "connu"}
