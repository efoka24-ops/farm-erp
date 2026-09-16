import 'dart:convert';

import '../api/api_client.dart';
import 'sync_queue_repository.dart';

/// Rejoue la file d'actions locale vers `/api/sync/push` (T013). Appelé au
/// retour de connexion (écoute connectivité côté écran d'accueil) ou
/// manuellement ; idempotent, donc sans risque à rejouer plusieurs fois si
/// une tentative précédente a été interrompue par une coupure réseau.
class SyncClient {
  SyncClient(this._api, this._queue);

  final ApiClient _api;
  final SyncQueueRepository _queue;

  /// Retourne le nombre d'actions effectivement synchronisées.
  Future<int> synchroniser() async {
    final actions = await _queue.actionsEnAttente();

    if (actions.isEmpty) {
      return 0;
    }

    final reponse = await _api.post('/api/sync/push', {
      'actions': actions
          .map(
            (a) => {
              'id': a.id,
              'entity_type': a.entityType,
              'entity_id': a.entityId,
              'operation': a.operation,
              'payload': jsonDecode(a.payloadJson),
              'occurred_at': a.occurredAt.toIso8601String(),
            },
          )
          .toList(),
    });

    if (reponse.statusCode != 200) {
      throw SyncException(
        "Échec de synchronisation (${reponse.statusCode}) : rejouable sans perte de données.",
      );
    }

    final resultats = jsonDecode(reponse.body)['resultats'] as List;

    for (final resultat in resultats) {
      if (resultat['statut'] == 'applique' || resultat['deja_recue'] == true) {
        await _queue.marquerAppliquee(resultat['id'] as String);
      }
      // 'conflit' reste en file : à traiter à la prochaine ouverture, une
      // fois le gérant arbitré côté web (cf. SyncController::resoudreConflit).
    }

    return resultats.length;
  }
}

class SyncException implements Exception {
  SyncException(this.message);
  final String message;

  @override
  String toString() => message;
}
