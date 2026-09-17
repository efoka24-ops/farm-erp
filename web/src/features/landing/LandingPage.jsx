import { useState } from 'react';
import { api } from '../../api/client';

/**
 * Porte d'entrée publique de l'ERP : présentation, création de compte pour
 * sa ferme, et démo restreinte utilisable immédiatement (5 jours d'accès).
 */
export default function LandingPage({ onConnecte, onDemanderConnexion }) {
  const [formulaire, setFormulaire] = useState(null); // null | 'compte' | 'demo'

  if (formulaire) {
    return (
      <div className="app-shell centre">
        <InscriptionForm
          mode={formulaire}
          onConnecte={onConnecte}
          onAnnuler={() => setFormulaire(null)}
        />
      </div>
    );
  }

  return (
    <div className="landing">
      <header className="landing-hero">
        <h1>TRU FARM ERP</h1>
        <p>
          La plateforme tout-en-un pour gérer votre cheptel, votre comptabilité OHADA,
          votre santé vétérinaire et vos dossiers de financement — même hors connexion.
        </p>
        <div className="landing-actions">
          <button className="primaire" onClick={() => setFormulaire('compte')}>
            Créer un compte pour ma ferme
          </button>
          <button className="secondaire" onClick={() => setFormulaire('demo')}>
            Essayer la démo (5 jours, sans engagement)
          </button>
        </div>
        <button className="lien" onClick={onDemanderConnexion}>
          J'ai déjà un compte — Se connecter
        </button>
      </header>

      <section className="landing-fonctionnalites">
        <div className="carte-fonctionnalite">
          <h3>Cheptel hors ligne</h3>
          <p>Fiches animaux, pesées, incidents, QR TRU TRACE — utilisable sans réseau.</p>
        </div>
        <div className="carte-fonctionnalite">
          <h3>Comptabilité OHADA</h3>
          <p>Compte de résultat, bilan, coût de revient par animal — générés automatiquement.</p>
        </div>
        <div className="carte-fonctionnalite">
          <h3>Dossier de financement</h3>
          <p>PDF signé et horodaté, prêt pour BPR Rwanda/FIDA, en un clic.</p>
        </div>
      </section>
    </div>
  );
}

function InscriptionForm({ mode, onConnecte, onAnnuler }) {
  const [nomExploitation, setNomExploitation] = useState('');
  const [nom, setNom] = useState('');
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [erreur, setErreur] = useState(null);
  const [chargement, setChargement] = useState(false);

  const estDemo = mode === 'demo';
  const endpoint = estDemo ? '/api/auth/register-demo' : '/api/auth/register';

  async function soumettre(e) {
    e.preventDefault();
    setErreur(null);
    setChargement(true);

    try {
      const reponse = await api.post(endpoint, {
        nom_exploitation: nomExploitation,
        nom,
        email,
        password: motDePasse,
      });
      onConnecte(reponse.token, reponse.user);
    } catch (err) {
      setErreur(
        err.erreurs
          ? Object.values(err.erreurs).flat().join(' ')
          : err.message || 'Échec de la création du compte.'
      );
    } finally {
      setChargement(false);
    }
  }

  return (
    <form onSubmit={soumettre} className="login-form">
      <h1>{estDemo ? 'Démo — 5 jours d\'accès' : 'Créer mon compte'}</h1>
      {estDemo && (
        <p className="info-demo">
          Votre exploitation démo sera pré-remplie avec des données d'exemple.
          L'accès est automatiquement désactivé après 5 jours.
        </p>
      )}
      <label>
        Nom de l'exploitation
        <input type="text" value={nomExploitation} onChange={(e) => setNomExploitation(e.target.value)} required />
      </label>
      <label>
        Votre nom
        <input type="text" value={nom} onChange={(e) => setNom(e.target.value)} required />
      </label>
      <label>
        Email
        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
      </label>
      <label>
        Mot de passe
        <input type="password" minLength={8} value={motDePasse} onChange={(e) => setMotDePasse(e.target.value)} required />
      </label>
      {erreur && <p className="erreur">{erreur}</p>}
      <button type="submit" disabled={chargement}>
        {chargement ? 'Création…' : estDemo ? 'Démarrer la démo' : 'Créer mon compte'}
      </button>
      <button type="button" className="lien" onClick={onAnnuler}>Retour</button>
    </form>
  );
}
