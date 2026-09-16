import 'dart:io';

import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

part 'app_database.g.dart';

/// Fiche animal locale (T020) : miroir hors-ligne de `animaux` côté API.
/// [syncStatut] distingue les lignes créées localement et pas encore
/// synchronisées ('local') de celles déjà connues du serveur ('synchronise').
class Animaux extends Table {
  TextColumn get id => text()(); // UUID généré côté mobile, identique en id API
  TextColumn get truTraceId => text().nullable()();
  TextColumn get espece => text()();
  TextColumn get race => text().nullable()();
  TextColumn get sexe => text()();
  DateTimeColumn get dateNaissance => dateTime().nullable()();
  TextColumn get statut => text().withDefault(const Constant('actif'))();
  TextColumn get description => text().nullable()();
  TextColumn get syncStatut => text().withDefault(const Constant('local'))();
  DateTimeColumn get creeLe => dateTime().withDefault(currentDateAndTime)();

  @override
  Set<Column> get primaryKey => {id};
}

/// File d'actions offline (T013) : miroir local de `sync_actions` côté API.
/// Chaque écriture (création animal, pesée, incident, distribution) hors
/// ligne passe par cette table avant d'être rejouée via /api/sync/push.
class SyncQueue extends Table {
  TextColumn get id =>
      text()(); // UUID, réutilisé comme clé d'idempotence côté API
  TextColumn get entityType => text()();
  TextColumn get entityId => text()();
  TextColumn get operation => text()(); // create | update | delete
  TextColumn get payloadJson => text()();
  DateTimeColumn get occurredAt => dateTime()();
  TextColumn get statut => text().withDefault(const Constant('en_attente'))();

  @override
  Set<Column> get primaryKey => {id};
}

@DriftDatabase(tables: [Animaux, SyncQueue])
class AppDatabase extends _$AppDatabase {
  AppDatabase() : super(_ouvrirConnexion());

  AppDatabase.pourTests(super.executor);

  @override
  int get schemaVersion => 1;

  static LazyDatabase _ouvrirConnexion() {
    return LazyDatabase(() async {
      final dossier = await getApplicationDocumentsDirectory();
      final fichier = File(p.join(dossier.path, 'farm_erp_offline.sqlite'));

      return NativeDatabase.createInBackground(fichier);
    });
  }
}
