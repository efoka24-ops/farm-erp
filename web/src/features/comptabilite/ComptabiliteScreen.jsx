import { useEffect, useState } from 'react';
import { api } from '../../api/client';
import MouvementForm from './MouvementForm';

/**
 * Écran web comptable (T034) : saisie dépense/recette, consultation des
 * états financiers OHADA, export PDF (compte de résultat, bilan) et CSV
 * (mouvements, ouvrable dans Excel).
 */
export default function ComptabiliteScreen({ token }) {
  const [planComptable, setPlanComptable] = useState([]);
  const [depenses, setDepenses] = useState([]);
  const [recettes, setRecettes] = useState([]);
  const [compteResultat, setCompteResultat] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  async function recharger() {
    setChargement(true);
    setErreur(null);

    try {
      const [plan, depensesResp, recettesResp, resultat] = await Promise.all([
        api.get('/api/comptabilite/plan-comptable', token),
        api.get('/api/depenses', token),
        api.get('/api/recettes', token),
        api.get('/api/comptabilite/compte-resultat', token),
      ]);

      setPlanComptable(plan);
      setDepenses(depensesResp.data);
      setRecettes(recettesResp.data);
      setCompteResultat(resultat);
    } catch (err) {
      setErreur(err.message);
    } finally {
      setChargement(false);
    }
  }

  useEffect(() => {
    recharger();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const comptesCharges = planComptable.filter((c) => c.nature === 'charge');
  const comptesProduits = planComptable.filter((c) => c.nature === 'produit');

  return (
    <div className="comptabilite">
      <header>
        <h1>Comptabilité OHADA</h1>
        <div className="actions-export">
          <button onClick={() => api.telecharger('/api/comptabilite/compte-resultat/pdf', token, 'compte-resultat.pdf')}>
            Compte de résultat (PDF)
          </button>
          <button onClick={() => api.telecharger('/api/comptabilite/bilan/pdf', token, 'bilan.pdf')}>
            Bilan (PDF)
          </button>
          <button onClick={() => api.telecharger('/api/comptabilite/mouvements/export-csv', token, 'mouvements.csv')}>
            Mouvements (Excel/CSV)
          </button>
        </div>
      </header>

      {erreur && <p className="erreur">{erreur}</p>}

      {compteResultat && (
        <section className="resume">
          <div className="carte">
            <span>Produits du mois</span>
            <strong>{compteResultat.total_produits.toLocaleString('fr-FR')} RWF</strong>
          </div>
          <div className="carte">
            <span>Charges du mois</span>
            <strong>{compteResultat.total_charges.toLocaleString('fr-FR')} RWF</strong>
          </div>
          <div className={`carte resultat ${compteResultat.resultat_net >= 0 ? 'positif' : 'negatif'}`}>
            <span>Résultat net</span>
            <strong>{compteResultat.resultat_net.toLocaleString('fr-FR')} RWF</strong>
          </div>
        </section>
      )}

      <section className="saisie">
        <MouvementForm
          type="depense"
          comptes={comptesCharges}
          token={token}
          onEnregistre={recharger}
        />
        <MouvementForm
          type="recette"
          comptes={comptesProduits}
          token={token}
          onEnregistre={recharger}
        />
      </section>

      {chargement ? (
        <p>Chargement…</p>
      ) : (
        <section className="listes">
          <MouvementTable titre="Dernières dépenses" mouvements={depenses} />
          <MouvementTable titre="Dernières recettes" mouvements={recettes} />
        </section>
      )}
    </div>
  );
}

function MouvementTable({ titre, mouvements }) {
  return (
    <div>
      <h3>{titre}</h3>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Compte</th>
            <th>Libellé</th>
            <th>Montant</th>
          </tr>
        </thead>
        <tbody>
          {mouvements.map((m) => (
            <tr key={m.id}>
              <td>{m.date_operation}</td>
              <td>{m.compte_ohada.code}</td>
              <td>{m.libelle}</td>
              <td>{Number(m.montant).toLocaleString('fr-FR')} RWF</td>
            </tr>
          ))}
          {mouvements.length === 0 && (
            <tr>
              <td colSpan={4}>Aucun mouvement.</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
