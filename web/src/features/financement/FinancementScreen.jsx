import { useEffect, useState } from 'react';
import { api } from '../../api/client';

/**
 * Écran "Dossier de financement" (T042) : saisie montant/durée/objet,
 * vérification de complétude avant génération, téléchargement du PDF signé.
 */
export default function FinancementScreen({ token }) {
  const [completude, setCompletude] = useState(null);
  const [montant, setMontant] = useState('');
  const [dureeMois, setDureeMois] = useState('24');
  const [objet, setObjet] = useState('');
  const [dossiers, setDossiers] = useState([]);
  const [erreur, setErreur] = useState(null);
  const [manques, setManques] = useState([]);
  const [enCours, setEnCours] = useState(false);

  async function recharger() {
    const [c, liste] = await Promise.all([
      api.get('/api/financement/completude', token),
      api.get('/api/financement/dossiers', token),
    ]);
    setCompletude(c);
    setDossiers(liste);
  }

  useEffect(() => {
    recharger();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  async function genererDossier(e) {
    e.preventDefault();
    setErreur(null);
    setManques([]);
    setEnCours(true);

    try {
      await api.post('/api/financement/dossiers', {
        montant_demande: Number(montant),
        duree_mois: Number(dureeMois),
        objet,
      }, token);
      setMontant('');
      setObjet('');
      recharger();
    } catch (err) {
      if (err.erreurs?.completude) {
        setManques(err.erreurs.completude);
      } else {
        setErreur(err.message || 'Échec de la génération.');
      }
    } finally {
      setEnCours(false);
    }
  }

  return (
    <div className="financement">
      <h1>Dossier de financement</h1>

      {completude && !completude.complet && (
        <div className="alerte-manques">
          <strong>Données incomplètes pour générer un dossier :</strong>
          <ul>
            {completude.manques.map((m) => <li key={m}>{m}</li>)}
          </ul>
        </div>
      )}

      <form onSubmit={genererDossier} className="mouvement-form">
        <label>
          Montant demandé (RWF)
          <input type="number" min="1" value={montant} onChange={(e) => setMontant(e.target.value)} required />
        </label>
        <label>
          Durée (mois)
          <input type="number" min="1" max="120" value={dureeMois} onChange={(e) => setDureeMois(e.target.value)} required />
        </label>
        <label>
          Objet
          <input type="text" value={objet} onChange={(e) => setObjet(e.target.value)} required />
        </label>
        {erreur && <p className="erreur">{erreur}</p>}
        {manques.length > 0 && (
          <ul className="erreur">
            {manques.map((m) => <li key={m}>{m}</li>)}
          </ul>
        )}
        <button type="submit" disabled={enCours || (completude && !completude.complet)}>
          {enCours ? 'Génération…' : 'Générer le dossier (PDF signé)'}
        </button>
      </form>

      <h3>Dossiers générés</h3>
      <table>
        <thead>
          <tr><th>Date</th><th>Montant</th><th>Durée</th><th>Objet</th><th>Signature</th><th></th></tr>
        </thead>
        <tbody>
          {dossiers.map((d) => (
            <tr key={d.id}>
              <td>{new Date(d.created_at).toLocaleDateString('fr-FR')}</td>
              <td>{Number(d.montant_demande).toLocaleString('fr-FR')} RWF</td>
              <td>{d.duree_mois} mois</td>
              <td>{d.objet}</td>
              <td title={d.signature_sha256}>{d.signature_sha256.slice(0, 12)}…</td>
              <td>
                <button onClick={() => api.telecharger(`/api/financement/dossiers/${d.id}/telecharger`, token, 'dossier-financement.pdf')}>
                  Télécharger
                </button>
              </td>
            </tr>
          ))}
          {dossiers.length === 0 && (
            <tr><td colSpan={6}>Aucun dossier généré.</td></tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
