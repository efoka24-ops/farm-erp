<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de commande</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a1a1a; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .footer { margin-top: 40px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <h1>Bon de commande</h1>
    <p>{{ $exploitation->nom }} — généré le {{ $genereLe->format('d/m/Y') }}</p>

    <table>
        <tr><th>Article</th><td>{{ $categorie->nom }}</td></tr>
        <tr><th>Fournisseur</th><td>{{ $categorie->fournisseur_nom ?? 'Non renseigné' }}</td></tr>
        <tr><th>Contact</th><td>{{ $categorie->fournisseur_contact ?? '—' }}</td></tr>
        <tr><th>Quantité commandée</th><td>{{ $bonCommande->quantite_commandee }} {{ $categorie->unite }}</td></tr>
        <tr><th>Niveau actuel avant commande</th><td>{{ $niveauActuel }} {{ $categorie->unite }}</td></tr>
        <tr><th>Seuil d'alerte</th><td>{{ $categorie->seuil_alerte_quantite }} {{ $categorie->unite }}</td></tr>
    </table>

    <div class="footer">
        Document généré automatiquement par TRU FARM ERP suite au déclenchement de l'alerte de seuil.
    </div>
</body>
</html>
