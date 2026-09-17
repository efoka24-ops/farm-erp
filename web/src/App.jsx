import { useEffect, useState } from 'react';
import './App.css';
import LoginForm from './features/auth/LoginForm';
import ComptabiliteScreen from './features/comptabilite/ComptabiliteScreen';
import FinancementScreen from './features/financement/FinancementScreen';
import LandingPage from './features/landing/LandingPage';
import PlanificateurScreen from './features/planificateur/PlanificateurScreen';

const CLE_STOCKAGE = 'trufarm_token';

const ONGLETS = {
  comptabilite: { label: 'Comptabilité', Composant: ComptabiliteScreen },
  financement: { label: 'Financement', Composant: FinancementScreen },
  planificateur: { label: 'Vendre maintenant', Composant: PlanificateurScreen },
};

function App() {
  const [token, setToken] = useState(() => localStorage.getItem(CLE_STOCKAGE));
  const [utilisateur, setUtilisateur] = useState(null);
  const [onglet, setOnglet] = useState('comptabilite');
  const [vuePublique, setVuePublique] = useState('landing'); // 'landing' | 'login'

  useEffect(() => {
    if (token) {
      localStorage.setItem(CLE_STOCKAGE, token);
    } else {
      localStorage.removeItem(CLE_STOCKAGE);
    }
  }, [token]);

  function connecter(t, u) {
    setToken(t);
    setUtilisateur(u);
  }

  function deconnecter() {
    setToken(null);
    setUtilisateur(null);
    setVuePublique('landing');
  }

  if (!token) {
    if (vuePublique === 'login') {
      return (
        <main className="app-shell centre">
          <LoginForm onConnecte={connecter} />
          <button className="lien" onClick={() => setVuePublique('landing')}>
            ← Retour à l'accueil
          </button>
        </main>
      );
    }

    return <LandingPage onConnecte={connecter} onDemanderConnexion={() => setVuePublique('login')} />;
  }

  const { Composant } = ONGLETS[onglet];
  const enDemo = utilisateur?.exploitation?.mode === 'demo';

  return (
    <main className="app-shell">
      {enDemo && (
        <div className="bandeau-demo">
          Compte démo — accès valable jusqu'au{' '}
          {new Date(utilisateur.exploitation.essai_expire_le).toLocaleDateString('fr-FR')}.
        </div>
      )}
      <nav className="barre-superieure">
        <strong>TRU FARM ERP</strong>
        {Object.entries(ONGLETS).map(([cle, { label }]) => (
          <button
            key={cle}
            className={cle === onglet ? 'onglet actif' : 'onglet'}
            onClick={() => setOnglet(cle)}
          >
            {label}
          </button>
        ))}
        {utilisateur && <span>{utilisateur.name}</span>}
        <button onClick={deconnecter}>Déconnexion</button>
      </nav>
      <Composant token={token} />
    </main>
  );
}

export default App;
