import 'package:flutter/material.dart';

import '../../core/db/app_database.dart';

/// Dashboard exécutif (T026) : chiffres clés calculés localement pour un
/// affichage instantané hors ligne (< 2s/3G visé, cf. T099). Les chiffres
/// serveur (toutes exploitations, alertes croisées) arrivent après sync ;
/// cet écran reste utilisable dès l'ouverture de l'app, sans réseau.
class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key, required this.db});

  final AppDatabase db;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tableau de bord')),
      body: FutureBuilder<_ChiffresCles>(
        future: _charger(),
        builder: (context, snapshot) {
          if (!snapshot.hasData) {
            return const Center(child: CircularProgressIndicator());
          }

          final chiffres = snapshot.data!;

          return Padding(
            padding: const EdgeInsets.all(16),
            child: GridView.count(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              children: [
                _CarteChiffre(label: 'Animaux', valeur: chiffres.totalAnimaux),
                _CarteChiffre(
                  label: 'À synchroniser',
                  valeur: chiffres.enAttenteSync,
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Future<_ChiffresCles> _charger() async {
    // Volumes attendus (quelques centaines d'animaux max par exploitation) :
    // un simple .get().length reste largement sous le budget de 2s/3G visé
    // (T099), pas besoin d'une requête d'agrégation SQL ici.
    final totalAnimaux = (await db.select(db.animaux).get()).length;
    final enAttente = (await (db.select(
      db.syncQueue,
    )..where((t) => t.statut.equals('en_attente'))).get()).length;

    return _ChiffresCles(totalAnimaux: totalAnimaux, enAttenteSync: enAttente);
  }
}

class _ChiffresCles {
  _ChiffresCles({required this.totalAnimaux, required this.enAttenteSync});
  final int totalAnimaux;
  final int enAttenteSync;
}

class _CarteChiffre extends StatelessWidget {
  const _CarteChiffre({required this.label, required this.valeur});

  final String label;
  final int valeur;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('$valeur', style: Theme.of(context).textTheme.headlineMedium),
            Text(label),
          ],
        ),
      ),
    );
  }
}
