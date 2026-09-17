from __future__ import annotations

from fastapi.testclient import TestClient

from src.main import app, prix_marche_service


def test_health():
    with TestClient(app) as client:
        reponse = client.get("/health")
        assert reponse.status_code == 200
        assert reponse.json() == {"status": "ok"}


def test_recommandations_ventes_avec_prix_fourni():
    with TestClient(app) as client:
        reponse = client.post(
            "/recommandations-ventes",
            json={
                "animaux": [
                    {
                        "id": "a1", "espece": "bovin", "poids_actuel_kg": 300,
                        "cout_total": 100000, "age_jours": 200, "prix_marche_kg": 1000,
                    }
                ]
            },
        )

        assert reponse.status_code == 200
        recommandations = reponse.json()["recommandations"]
        assert len(recommandations) == 1
        assert recommandations[0]["id"] == "a1"
        assert "score" in recommandations[0]


def test_recommandations_ventes_sans_prix_ni_fallback_retourne_422():
    with TestClient(app) as client:
        reponse = client.post(
            "/recommandations-ventes",
            json={
                "animaux": [
                    {
                        "id": "a1", "espece": "espece_jamais_vue", "poids_actuel_kg": 300,
                        "cout_total": 100000, "age_jours": 200,
                    }
                ]
            },
        )

        assert reponse.status_code == 422


def test_prix_marche_enregistrer_puis_recuperer(tmp_path, monkeypatch):
    fichier_temporaire = tmp_path / "prix_marche.json"
    monkeypatch.setattr(prix_marche_service, "fichier", fichier_temporaire)

    with TestClient(app) as client:
        client.post("/prix-marche", json={"espece": "caprin", "prix_kg": 1500})

        reponse = client.get("/prix-marche/caprin")

        assert reponse.status_code == 200
        assert reponse.json()["prix_kg"] == 1500
        assert reponse.json()["source"] == "connu"


def test_prix_marche_inconnu_retourne_404(tmp_path, monkeypatch):
    fichier_temporaire = tmp_path / "prix_marche.json"
    monkeypatch.setattr(prix_marche_service, "fichier", fichier_temporaire)

    with TestClient(app) as client:
        reponse = client.get("/prix-marche/espece_jamais_configuree")
        assert reponse.status_code == 404
