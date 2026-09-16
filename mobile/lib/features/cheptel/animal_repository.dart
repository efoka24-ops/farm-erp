import 'package:drift/drift.dart';
import 'package:uuid/uuid.dart';

import '../../core/db/app_database.dart';
import '../../core/sync/sync_queue_repository.dart';

/// CRUD animal offline-first (T020/T022 côté mobile) : toute écriture va
/// d'abord dans la table locale `Animaux` (utilisable immédiatement, hors
/// connexion), puis est enfilée dans la file de sync pour rejeu vers l'API.
class AnimalRepository {
  AnimalRepository(this._db, this._syncQueue);

  final AppDatabase _db;
  final SyncQueueRepository _syncQueue;
  final _uuid = const Uuid();

  Stream<List<AnimauxData>> observerTous() {
    return (_db.select(
      _db.animaux,
    )..orderBy([(t) => OrderingTerm.desc(t.creeLe)])).watch();
  }

  Future<AnimauxData> creer({
    required String espece,
    String? race,
    required String sexe,
    DateTime? dateNaissance,
    String? description,
  }) async {
    final id = _uuid.v4();
    final truTraceId = 'TRU-LOCAL-${id.substring(0, 8).toUpperCase()}';

    final companion = AnimauxCompanion.insert(
      id: id,
      truTraceId: Value(truTraceId),
      espece: espece,
      race: Value(race),
      sexe: sexe,
      dateNaissance: Value(dateNaissance),
      description: Value(description),
    );

    await _db.into(_db.animaux).insert(companion);

    await _syncQueue.enfiler(
      entityType: 'animal',
      entityId: id,
      operation: 'create',
      payload: {
        'id': id,
        'espece': espece,
        'race': race,
        'sexe': sexe,
        'date_naissance': dateNaissance?.toIso8601String(),
        'description': description,
      },
    );

    return (_db.select(_db.animaux)..where((t) => t.id.equals(id))).getSingle();
  }

  /// Signalement 1-touche (T024) : un seul champ requis (animal), le reste
  /// est pré-rempli côté formulaire ("maladie"/"moyenne" par défaut, cf.
  /// IncidentController::store côté API qui applique les mêmes défauts).
  Future<void> signalerMalade(String animalId, {String? description}) async {
    await _syncQueue.enfiler(
      entityType: 'incident',
      entityId: _uuid.v4(),
      operation: 'create',
      payload: {
        'animal_id': animalId,
        'type': 'maladie',
        'gravite': 'moyenne',
        'description': description,
      },
    );
  }
}
