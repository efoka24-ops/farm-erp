<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de vente</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a1a1a; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .montant { font-size: 18px; font-weight: bold; color: #1a7f37; margin-top: 16px; }
        .certificat { margin-top: 24px; padding: 12px; border: 1px dashed #999; font-size: 11px; }
        .footer { margin-top: 40px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <h1>Bon de vente — {{ $exploitation->nom }}</h1>
    <p>Date : {{ $vente->date_vente->format('d/m/Y') }}</p>

    <table>
        <tr><th>Animal</th><td>{{ $animal->espece }} — ID TRU TRACE {{ $animal->tru_trace_id }}</td></tr>
        <tr><th>Acheteur</th><td>{{ $vente->client->nom ?? $vente->client_nom_libre ?? 'Non renseigné' }}</td></tr>
        <tr><th>Mode de paiement</th><td>{{ $vente->mode_paiement }}</td></tr>
        @if ($vente->tva_applicable)
            <tr><th>Montant HT</th><td>{{ number_format($vente->montant, 0, ',', ' ') }} RWF</td></tr>
            <tr><th>TVA ({{ $vente->taux_tva_pourcent }}%)</th><td>{{ number_format($vente->montantTtc() - $vente->montant, 0, ',', ' ') }} RWF</td></tr>
        @endif
    </table>

    <p class="montant">Montant {{ $vente->tva_applicable ? 'TTC' : '' }} : {{ number_format($vente->montantTtc(), 0, ',', ' ') }} RWF</p>

    <div class="certificat">
        <strong>Certificat de traçabilité TRU TRACE</strong><br>
        Référence : {{ $certificat['reference'] }}<br>
        Empreinte : {{ $certificat['empreinte_sha256'] }}<br>
        @if (! $certificat['certifie_officiellement'])
            <em>{{ $certificat['note'] }}</em>
        @endif
    </div>

    <div class="footer">Document généré automatiquement par TRU FARM ERP.</div>
</body>
</html>
