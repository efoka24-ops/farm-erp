import { useEffect, useState } from 'react';
import './App.css';
import LoginForm from './features/auth/LoginForm';
import ComptabiliteScreen from './features/comptabilite/ComptabiliteScreen';

const CLE_STOCKAGE = 'trufarm_token';

function App() {
  const [token, setToken] = useState(() => localStorage.getItem(CLE_STOCKAGE));
  const [utilisateur, setUtilisateur] = useState(null);

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

  return (
    <main className="app-shell">
      <nav className="barre-superieure">
        <strong>TRU FARM ERP</strong>
        {utilisateur && <span>{utilisateur.name}</span>}
        <button onClick={deconnecter}>Déconnexion</button>
      </nav>
      <ComptabiliteScreen token={token} />
    </main>
  );
}

export default App;
