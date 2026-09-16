import { useEffect, useState } from 'react';
import './App.css';
import LoginForm from './features/auth/LoginForm';
import ComptabiliteScreen from './features/comptabilite/ComptabiliteScreen';
import FinancementScreen from './features/financement/FinancementScreen';

const CLE_STOCKAGE = 'trufarm_token';

const ONGLETS = {
  comptabilite: { label: 'Comptabilité', Composant: ComptabiliteScreen },
  financement: { label: 'Financement', Composant: FinancementScreen },
};

function App() {
  const [token, setToken] = useState(() => localStorage.getItem(CLE_STOCKAGE));
  const [utilisateur, setUtilisateur] = useState(null);
  const [onglet, setOnglet] = useState('comptabilite');

  useEffect(() => {
    if (token) {
      localStorage.setItem(CLE_STOCKAGE, token);
    } else {
      localStorage.removeItem(CLE_STOCKAGE);
    }
  }, [token]);

  function deconnecter() {
    setToken(null);
    setUtilisateur(null);
  }

  if (!token) {
    return (
      <main className="app-shell centre">
        <LoginForm onConnecte={(t, u) => { setToken(t); setUtilisateur(u); }} />
      </main>
    );
  }

  const { Composant } = ONGLETS[onglet];

  return (
    <main className="app-shell">
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
