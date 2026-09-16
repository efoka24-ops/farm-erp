<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Dossier de financement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 15px; margin-top: 28px; border-bottom: 2px solid #1a7f37; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 5px 7px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        .montant { text-align: right; }
        .page-garde { text-align: center; margin-top: 120px; }
        .page-garde .montant-demande { font-size: 26px; font-weight: bold; color: #1a7f37; margin: 24px 0; }
        .attestation { margin-top: 24px; padding: 16px; border: 1px solid #ddd; font-style: italic; }
        .footer { margin-top: 40px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="page-garde">
        <h1>DOSSIER DE FINANCEMENT</h1>
        <p>{{ $exploitation->nom }}</p>
        <div class="montant-demande">{{ number_format($montantDemande, 0, ',', ' ') }} RWF</div>
        <p>Objet : {{ $objet }}</p>
        <p>Durée : {{ $dureeMois }} mois — Mensualité estimée : {{ number_format($mensualite, 0, ',', ' ') }} RWF</p>
        <p>Généré le {{ $genereLe->format('d/m/Y à H:i') }}</p>
    </div>

    <div style="page-break-before: always;"></div>

    <h2>1. Comptes OHADA — 3 derniers exercices</h2>
    <table>
        <tr><th>Exercice</th><th class="montant">Produits</th><th class="montant">Charges</th><th class="montant">Résultat net</th></tr>
        @foreach ($comptesTroisAns as $compte)
            <tr>
                <td>{{ $compte['annee'] }}</td>
                <td class="montant">{{ number_format($compte['total_produits'], 0, ',', ' ') }}</td>
                <td class="montant">{{ number_format($compte['total_charges'], 0, ',', ' ') }}</td>
                <td class="montant">{{ number_format($compte['resultat_net'], 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    </table>

    <h2>2. Registre cheptel (TRU TRACE)</h2>
    <table>
        <tr><th>ID TRU TRACE</th><th>Espèce</th><th>Race</th><th>Statut</th></tr>
        @foreach ($animaux as $animal)
            <tr>
                <td>{{ $animal->tru_trace_id }}</td>
                <td>{{ $animal->espece }}</td>
                <td>{{ $animal->race ?? '—' }}</td>
                <td>{{ $animal->statut }}</td>
            </tr>
        @endforeach
    </table>
    <p>Total : {{ $animaux->count() }} animal(aux) enregistré(s).</p>

    <h2>3. Bilan sanitaire</h2>
    <table>
        <tr><td>Vaccinations administrées</td><td class="montant">{{ $bilanSanitaire['vaccinations_administrees'] }}</td></tr>
        <tr><td>Traitements en cours (délai d'attente)</td><td class="montant">{{ $bilanSanitaire['traitements_en_cours'] }}</td></tr>
        <tr><td>Incidents critiques ouverts</td><td class="montant">{{ $bilanSanitaire['incidents_critiques_ouverts'] }}</td></tr>
    </table>

    <h2>4. Plan d'investissement et de remboursement</h2>
    <p>Montant demandé : <strong>{{ number_format($montantDemande, 0, ',', ' ') }} RWF</strong> —
       Objet : {{ $objet }} — Taux indicatif : {{ $tauxAnnuelPourcent }} %/an sur {{ $dureeMois }} mois.</p>
    <table>
        <tr><th>Mois</th><th class="montant">Mensualité</th><th class="montant">Capital</th><th class="montant">Intérêt</th><th class="montant">Solde restant</th></tr>
        @foreach (array_slice($echeancier, 0, 12) as $ligne)
            <tr>
                <td>{{ $ligne['mois'] }}</td>
                <td class="montant">{{ number_format($ligne['mensualite'], 0, ',', ' ') }}</td>
                <td class="montant">{{ number_format($ligne['capital'], 0, ',', ' ') }}</td>
                <td class="montant">{{ number_format($ligne['interet'], 0, ',', ' ') }}</td>
                <td class="montant">{{ number_format($ligne['solde_restant'], 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    </table>
    @if (count($echeancier) > 12)
        <p><em>… {{ count($echeancier) - 12 }} échéance(s) supplémentaire(s), voir échéancier complet en annexe numérique.</em></p>
    @endif

    <h2>5. Attestation TRU GROUP</h2>
    <div class="attestation">
        TRU GROUP atteste que les données ci-dessus sont issues directement de la plateforme
        TRU FARM ERP, exploitées par {{ $exploitation->nom }}, et n'ont fait l'objet d'aucune
        modification manuelle postérieure à leur saisie. Ce document est généré automatiquement
        et scellé par une empreinte SHA-256 (voir footer).
    </div>

    <div class="footer">
        Document généré automatiquement par TRU FARM ERP — l'empreinte SHA-256 de ce PDF est
        vérifiable via l'API (`/api/financement/dossiers/{id}/verifier`).
    </div>
</body>
</html>
