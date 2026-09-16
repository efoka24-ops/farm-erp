import { useState } from 'react';
import { api } from '../../api/client';

export default function LoginForm({ onConnecte }) {
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [erreur, setErreur] = useState(null);
  const [chargement, setChargement] = useState(false);

  async function soumettre(e) {
    e.preventDefault();
    setErreur(null);
    setChargement(true);

    try {
      const reponse = await api.post('/api/auth/login', { email, password: motDePasse });

      if (reponse.otp_required) {
        setErreur('Un code de vérification a été envoyé par email (2FA).');
        return;
      }

      onConnecte(reponse.token, reponse.user);
    } catch (err) {
      setErreur(err.message || 'Connexion impossible.');
    } finally {
      setChargement(false);
    }
  }

  return (
    <form onSubmit={soumettre} className="login-form">
      <h1>TRU FARM ERP</h1>
      <label>
        Email
        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
      </label>
      <label>
        Mot de passe
        <input type="password" value={motDePasse} onChange={(e) => setMotDePasse(e.target.value)} required />
      </label>
      {erreur && <p className="erreur">{erreur}</p>}
      <button type="submit" disabled={chargement}>
        {chargement ? 'Connexion…' : 'Se connecter'}
      </button>
    </form>
  );
}
