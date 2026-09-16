<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Compte de résultat</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .periode { color: #555; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        td.montant, th.montant { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .resultat { margin-top: 24px; padding: 12px; background: #f5f5f5; font-size: 16px; font-weight: bold; }
        .resultat.positif { color: #1a7f37; }
        .resultat.negatif { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Compte de résultat — {{ $exploitation->nom }}</h1>
    <p class="periode">Période du {{ $etat['periode']['debut'] }} au {{ $etat['periode']['fin'] }}</p>

    <h3>Produits (classe 7)</h3>
    <table>
        <tr><th>Compte</th><th>Libellé</th><th class="montant">Montant (RWF)</th></tr>
        @foreach ($etat['produits'] as $ligne)
            <tr>
                <td>{{ $ligne['compte'] }}</td>
                <td>{{ $ligne['libelle'] }}</td>
                <td class="montant">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">Total produits</td>
            <td class="montant">{{ number_format($etat['total_produits'], 0, ',', ' ') }}</td>
        </tr>
    </table>

    <h3>Charges (classe 6)</h3>
    <table>
        <tr><th>Compte</th><th>Libellé</th><th class="montant">Montant (RWF)</th></tr>
        @foreach ($etat['charges'] as $ligne)
            <tr>
                <td>{{ $ligne['compte'] }}</td>
                <td>{{ $ligne['libelle'] }}</td>
                <td class="montant">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">Total charges</td>
            <td class="montant">{{ number_format($etat['total_charges'], 0, ',', ' ') }}</td>
        </tr>
    </table>

    <div class="resultat {{ $etat['resultat_net'] >= 0 ? 'positif' : 'negatif' }}">
        Résultat net : {{ number_format($etat['resultat_net'], 0, ',', ' ') }} RWF
    </div>
</body>
</html>
