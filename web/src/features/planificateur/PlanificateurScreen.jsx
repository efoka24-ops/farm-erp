import { useEffect, useState } from 'react';
import { api } from '../../api/client';

/**
 * Écran "Vendre maintenant" (T072) : liste des animaux recommandés à la
 * vente par le planificateur IA, triés par score, avec un raccourci direct
 * vers l'enregistrement de la vente (US10).
 */
export default function PlanificateurScreen({ token }) {
  const [recommandations, setRecommandations] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    charger();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  async function charger() {
    setChargement(true);
    setErreur(null);

    try {
      const reponse = await api.get('/api/planificateur-ventes/recommandations', token);
      setRecommandations(reponse.recommandations || []);
    } catch (err) {
      setErreur(err.message || 'Recommandations indisponibles.');
    } finally {
      setChargement(false);
    }
  }

  async function creerLaVenteMaintenant(reco) {
    try {
      await api.post('/api/ventes', {
        animal_id: reco.id,
        montant: reco.prix_estime,
        date_vente: new Date().toISOString().slice(0, 10),
      }, token);
      charger();
    } catch (err) {
      setErreur(err.message || 'Échec de la création de la vente.');
    }
  }

  return (
    <div className="planificateur">
      <h1>Vendre maintenant</h1>
      <p className="sous-titre">
        Recommandations basées sur la marge journalière et le poids optimal de vente
        (voir la note sur la méthode dans la documentation).
      </p>

      {erreur && <p className="erreur">{erreur}</p>}

      {chargement ? (
        <p>Calcul des recommandations…</p>
      ) : recommandations.length === 0 ? (
        <p>Aucune recommandation pour le moment.</p>
      ) : (
        <table>
          <thead>
            <tr>
              <th>Animal</th>
              <th>Score</th>
              <th>Marge journalière</th>
              <th>Prix estimé</th>
              <th>CA potentiel</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {recommandations.map((r) => (
              <tr key={r.id}>
                <td>{r.animal?.tru_trace_id ?? r.id}</td>
                <td>{r.score}</td>
                <td>{Number(r.marge_journaliere).toLocaleString('fr-FR')} RWF/j</td>
                <td>{Number(r.prix_estime).toLocaleString('fr-FR')} RWF</td>
                <td>{Number(r.ca_potentiel).toLocaleString('fr-FR')} RWF</td>
                <td>
                  <button onClick={() => creerLaVenteMaintenant(r)}>Créer la vente maintenant</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
