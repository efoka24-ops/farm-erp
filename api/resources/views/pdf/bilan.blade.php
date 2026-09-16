<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bilan simplifié</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .au { color: #555; margin-top: 4px; }
        table { width: 48%; border-collapse: collapse; margin-top: 16px; display: inline-table; vertical-align: top; }
        table + table { margin-left: 4%; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        td.montant, th.montant { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .equilibre { margin-top: 24px; padding: 8px 12px; font-weight: bold; }
        .equilibre.ok { color: #1a7f37; }
        .equilibre.ko { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Bilan simplifié — {{ $exploitation->nom }}</h1>
    <p class="au">Au {{ $etat['au'] }}</p>

    <table>
        <tr><th colspan="2">ACTIF</th></tr>
        <tr><td>Trésorerie</td><td class="montant">{{ number_format($etat['actif']['tresorerie'], 0, ',', ' ') }}</td></tr>
        <tr class="total-row"><td>Total actif</td><td class="montant">{{ number_format($etat['actif']['total'], 0, ',', ' ') }}</td></tr>
    </table>

    <table>
        <tr><th colspan="2">PASSIF</th></tr>
        <tr><td>Résultat cumulé</td><td class="montant">{{ number_format($etat['passif']['resultat_cumule'], 0, ',', ' ') }}</td></tr>
        <tr class="total-row"><td>Total passif</td><td class="montant">{{ number_format($etat['passif']['total'], 0, ',', ' ') }}</td></tr>
    </table>

    <p class="equilibre {{ $etat['equilibre'] ? 'ok' : 'ko' }}">
        {{ $etat['equilibre'] ? '✓ Bilan équilibré' : '✗ Bilan déséquilibré — vérifier la saisie' }}
    </p>
</body>
</html>
