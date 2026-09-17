<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Formulaire de demande MINAGRI</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a1a1a; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; border: 1px solid #ddd; text-align: left; }
        .footer { margin-top: 40px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Demande d'éligibilité — {{ $libelleProgramme }}</h1>
    <p>Exploitation : {{ $exploitation->nom }} — {{ $exploitation->region }} {{ $exploitation->district }}</p>

    <table>
        <tr><th>Programme</th><td>{{ $libelleProgramme }}</td></tr>
        <tr><th>Éligible depuis</th><td>{{ $demande->eligible_depuis->format('d/m/Y') }}</td></tr>
        <tr><th>Critères</th><td>{{ $criteres }}</td></tr>
    </table>

    <h3>Pièces justificatives — Registre cheptel TRU TRACE</h3>
    <table>
        <tr><th>ID TRU TRACE</th><th>Espèce</th><th>Statut</th></tr>
        @foreach ($animaux as $animal)
            <tr><td>{{ $animal->tru_trace_id }}</td><td>{{ $animal->espece }}</td><td>{{ $animal->statut }}</td></tr>
        @endforeach
    </table>

    <div class="footer">
        Document généré automatiquement par TRU FARM ERP. Les critères d'éligibilité affichés sont
        indicatifs et doivent être validés par un agent MINAGRI avant instruction du dossier.
    </div>
</body>
</html>
