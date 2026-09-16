import 'dart:convert';

import 'package:drift/drift.dart';
import 'package:uuid/uuid.dart';

import '../db/app_database.dart';

/// Enfile une action métier (création/màj/suppression) dans la file locale
/// (T013). L'écriture est toujours locale d'abord (offline-first) : la
/// synchronisation effective vers l'API est un processus séparé
/// (voir SyncClient), rejouable à tout moment sans perte ni doublon grâce
/// à l'UUID généré ici et réutilisé comme clé d'idempotence côté serveur.
class SyncQueueRepository {
  SyncQueueRepository(this._db);

  final AppDatabase _db;
  final _uuid = const Uuid();

  Future<String> enfiler({
    required String entityType,
    required String entityId,
    required String operation,
    required Map<String, dynamic> payload,
  }) async {
    final id = _uuid.v4();

    await _db
        .into(_db.syncQueue)
        .insert(
          SyncQueueCompanion.insert(
            id: id,
            entityType: entityType,
            entityId: entityId,
            operation: operation,
            payloadJson: jsonEncode(payload),
            occurredAt: DateTime.now().toUtc(),
          ),
        );

    return id;
  }

  Future<List<SyncQueueData>> actionsEnAttente() {
    return (_db.select(
      _db.syncQueue,
    )..where((t) => t.statut.equals('en_attente'))).get();
  }

  Future<void> marquerAppliquee(String id) {
    return (_db.update(_db.syncQueue)..where((t) => t.id.equals(id))).write(
      const SyncQueueCompanion(statut: Value('applique')),
    );
  }
}
