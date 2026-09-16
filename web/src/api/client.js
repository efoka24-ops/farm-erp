const BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

function headers(token) {
  return {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };
}

async function traiter(reponse) {
  if (reponse.status === 204) return null;

  const corps = await reponse.json().catch(() => null);

  if (!reponse.ok) {
    const erreur = new Error(corps?.message || `Erreur ${reponse.status}`);
    erreur.status = reponse.status;
    erreur.erreurs = corps?.errors;
    throw erreur;
  }

  return corps;
}

export const api = {
  get: (chemin, token) => fetch(`${BASE_URL}${chemin}`, { headers: headers(token) }).then(traiter),
  post: (chemin, corps, token) =>
    fetch(`${BASE_URL}${chemin}`, { method: 'POST', headers: headers(token), body: JSON.stringify(corps) }).then(traiter),
  telecharger: async (chemin, token, nomFichier) => {
    const reponse = await fetch(`${BASE_URL}${chemin}`, { headers: headers(token) });
    if (!reponse.ok) throw new Error(`Erreur ${reponse.status}`);
    const blob = await reponse.blob();
    const url = URL.createObjectURL(blob);
    const lien = document.createElement('a');
    lien.href = url;
    lien.download = nomFichier;
    lien.click();
    URL.revokeObjectURL(url);
  },
};
