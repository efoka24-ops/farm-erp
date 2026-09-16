import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../core/db/app_database.dart';
import 'animal_repository.dart';
import 'signaler_malade_screen.dart';

/// Écran fiche animal (T021) : liste consultable hors ligne + création,
/// QR TRU TRACE affiché pour chaque animal (T025).
class FicheAnimalScreen extends StatelessWidget {
  const FicheAnimalScreen({super.key, required this.repository});

  final AnimalRepository repository;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Cheptel')),
      body: StreamBuilder<List<AnimauxData>>(
        stream: repository.observerTous(),
        builder: (context, snapshot) {
          final animaux = snapshot.data ?? [];

          if (animaux.isEmpty) {
            return const Center(child: Text('Aucun animal enregistré.'));
          }

          return ListView.builder(
            itemCount: animaux.length,
            itemBuilder: (context, index) {
              final animal = animaux[index];

              return ListTile(
                leading: CircleAvatar(
                  child: Text(animal.espece[0].toUpperCase()),
                ),
                title: Text(
                  '${animal.espece} — ${animal.race ?? 'race inconnue'}',
                ),
                subtitle: Text(animal.truTraceId ?? animal.id),
                trailing: animal.syncStatut == 'local'
                    ? const Icon(Icons.cloud_off, size: 18)
                    : const Icon(Icons.cloud_done, size: 18),
                onTap: () => _ouvrirFiche(context, animal),
              );
            },
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _ouvrirFormulaireCreation(context),
        icon: const Icon(Icons.add),
        label: const Text('Nouvel animal'),
      ),
    );
  }

  void _ouvrirFiche(BuildContext context, AnimauxData animal) {
    showModalBottomSheet(
      context: context,
      builder: (_) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              animal.truTraceId ?? animal.id,
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            QrImageView(
              data: 'trufarm://animal/${animal.truTraceId ?? animal.id}',
              size: 180,
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              icon: const Icon(Icons.sick),
              label: const Text('Signaler un animal malade'),
              onPressed: () {
                Navigator.of(context).pop();
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => SignalerMaladeScreen(
                      repository: repository,
                      animalId: animal.id,
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  void _ouvrirFormulaireCreation(BuildContext context) {
    final especeController = TextEditingController();
    final raceController = TextEditingController();
    String sexe = 'femelle';

    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Nouvel animal'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: especeController,
              decoration: const InputDecoration(labelText: 'Espèce'),
            ),
            TextField(
              controller: raceController,
              decoration: const InputDecoration(labelText: 'Race'),
            ),
            DropdownButtonFormField<String>(
              initialValue: sexe,
              items: const [
                DropdownMenuItem(value: 'femelle', child: Text('Femelle')),
                DropdownMenuItem(value: 'male', child: Text('Mâle')),
              ],
              onChanged: (value) => sexe = value ?? 'femelle',
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () async {
              if (especeController.text.trim().isEmpty) return;

              await repository.creer(
                espece: especeController.text.trim(),
                race: raceController.text.trim().isEmpty
                    ? null
                    : raceController.text.trim(),
                sexe: sexe,
              );

              if (dialogContext.mounted) Navigator.pop(dialogContext);
            },
            child: const Text('Créer (hors ligne)'),
          ),
        ],
      ),
    );
  }
}
