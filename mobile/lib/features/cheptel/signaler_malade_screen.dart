import 'package:flutter/material.dart';

import 'animal_repository.dart';

/// "Signaler un animal malade" en une touche (T024) : le bouton principal
/// enregistre immédiatement l'incident (type/gravité par défaut), sans
/// connexion requise. Le champ description est optionnel, à remplir après
/// coup si besoin.
class SignalerMaladeScreen extends StatefulWidget {
  const SignalerMaladeScreen({
    super.key,
    required this.repository,
    required this.animalId,
  });

  final AnimalRepository repository;
  final String animalId;

  @override
  State<SignalerMaladeScreen> createState() => _SignalerMaladeScreenState();
}

class _SignalerMaladeScreenState extends State<SignalerMaladeScreen> {
  final _descriptionController = TextEditingController();
  bool _enregistre = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Signaler un animal malade')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (_enregistre) ...[
              const Icon(Icons.check_circle, color: Colors.green, size: 64),
              const SizedBox(height: 16),
              const Text(
                'Incident enregistré (hors ligne). Il sera synchronisé automatiquement.',
              ),
            ] else ...[
              const Icon(Icons.sick, size: 64, color: Colors.orange),
              const SizedBox(height: 24),
              TextField(
                controller: _descriptionController,
                decoration: const InputDecoration(
                  labelText: 'Description (optionnel)',
                  border: OutlineInputBorder(),
                ),
                maxLines: 3,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 64,
                child: FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.red.shade700,
                  ),
                  icon: const Icon(Icons.report, size: 28),
                  label: const Text(
                    'Signaler maintenant',
                    style: TextStyle(fontSize: 18),
                  ),
                  onPressed: _enregistrerIncident,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _enregistrerIncident() async {
    await widget.repository.signalerMalade(
      widget.animalId,
      description: _descriptionController.text.trim().isEmpty
          ? null
          : _descriptionController.text.trim(),
    );

    if (mounted) setState(() => _enregistre = true);
  }
}
