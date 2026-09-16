import { useState } from 'react';
import { api } from '../../api/client';

const LIBELLES = { depense: 'Nouvelle dépense', recette: 'Nouvelle recette' };
const ENDPOINTS = { depense: '/api/depenses', recette: '/api/recettes' };

export default function MouvementForm({ type, comptes, token, onEnregistre }) {
  const [compteOhadaId, setCompteOhadaId] = useState('');
  const [montant, setMontant] = useState('');
  const [libelle, setLibelle] = useState('');
  const [dateOperation, setDateOperation] = useState(new Date().toISOString().slice(0, 10));
  const [erreur, setErreur] = useState(null);
  const [enCours, setEnCours] = useState(false);

  async function soumettre(e) {
    e.preventDefault();
    setErreur(null);
    setEnCours(true);

    try {
      await api.post(
        ENDPOINTS[type],
        { compte_ohada_id: Number(compteOhadaId), montant: Number(montant), libelle, date_operation: dateOperation },
        token,
      );
      setMontant('');
      setLibelle('');
      setCompteOhadaId('');
      onEnregistre();
    } catch (err) {
      setErreur(err.message || 'Échec de la saisie.');
    } finally {
      setEnCours(false);
    }
  }

  return (
    <form onSubmit={soumettre} className="mouvement-form">
      <h3>{LIBELLES[type]}</h3>
      <label>
        Compte
        <select value={compteOhadaId} onChange={(e) => setCompteOhadaId(e.target.value)} required>
          <option value="" disabled>Choisir un compte</option>
          {comptes.map((c) => (
            <option key={c.id} value={c.id}>{c.code} — {c.libelle}</option>
          ))}
        </select>
      </label>
      <label>
        Libellé
        <input type="text" value={libelle} onChange={(e) => setLibelle(e.target.value)} required />
      </label>
      <label>
        Montant (RWF)
        <input type="number" min="0.01" step="0.01" value={montant} onChange={(e) => setMontant(e.target.value)} required />
      </label>
      <label>
        Date
        <input type="date" value={dateOperation} onChange={(e) => setDateOperation(e.target.value)} required />
      </label>
      {erreur && <p className="erreur">{erreur}</p>}
      <button type="submit" disabled={enCours}>{enCours ? 'Enregistrement…' : 'Enregistrer'}</button>
    </form>
  );
}
