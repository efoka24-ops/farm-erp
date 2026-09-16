// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_database.dart';

// ignore_for_file: type=lint
class $AnimauxTable extends Animaux with TableInfo<$AnimauxTable, AnimauxData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $AnimauxTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _truTraceIdMeta = const VerificationMeta(
    'truTraceId',
  );
  @override
  late final GeneratedColumn<String> truTraceId = GeneratedColumn<String>(
    'tru_trace_id',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _especeMeta = const VerificationMeta('espece');
  @override
  late final GeneratedColumn<String> espece = GeneratedColumn<String>(
    'espece',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _raceMeta = const VerificationMeta('race');
  @override
  late final GeneratedColumn<String> race = GeneratedColumn<String>(
    'race',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _sexeMeta = const VerificationMeta('sexe');
  @override
  late final GeneratedColumn<String> sexe = GeneratedColumn<String>(
    'sexe',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _dateNaissanceMeta = const VerificationMeta(
    'dateNaissance',
  );
  @override
  late final GeneratedColumn<DateTime> dateNaissance =
      GeneratedColumn<DateTime>(
        'date_naissance',
        aliasedName,
        true,
        type: DriftSqlType.dateTime,
        requiredDuringInsert: false,
      );
  static const VerificationMeta _statutMeta = const VerificationMeta('statut');
  @override
  late final GeneratedColumn<String> statut = GeneratedColumn<String>(
    'statut',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('actif'),
  );
  static const VerificationMeta _descriptionMeta = const VerificationMeta(
    'description',
  );
  @override
  late final GeneratedColumn<String> description = GeneratedColumn<String>(
    'description',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _syncStatutMeta = const VerificationMeta(
    'syncStatut',
  );
  @override
  late final GeneratedColumn<String> syncStatut = GeneratedColumn<String>(
    'sync_statut',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('local'),
  );
  static const VerificationMeta _creeLeMeta = const VerificationMeta('creeLe');
  @override
  late final GeneratedColumn<DateTime> creeLe = GeneratedColumn<DateTime>(
    'cree_le',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: false,
    defaultValue: currentDateAndTime,
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    truTraceId,
    espece,
    race,
    sexe,
    dateNaissance,
    statut,
    description,
    syncStatut,
    creeLe,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'animaux';
  @override
  VerificationContext validateIntegrity(
    Insertable<AnimauxData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('tru_trace_id')) {
      context.handle(
        _truTraceIdMeta,
        truTraceId.isAcceptableOrUnknown(
          data['tru_trace_id']!,
          _truTraceIdMeta,
        ),
      );
    }
    if (data.containsKey('espece')) {
      context.handle(
        _especeMeta,
        espece.isAcceptableOrUnknown(data['espece']!, _especeMeta),
      );
    } else if (isInserting) {
      context.missing(_especeMeta);
    }
    if (data.containsKey('race')) {
      context.handle(
        _raceMeta,
        race.isAcceptableOrUnknown(data['race']!, _raceMeta),
      );
    }
    if (data.containsKey('sexe')) {
      context.handle(
        _sexeMeta,
        sexe.isAcceptableOrUnknown(data['sexe']!, _sexeMeta),
      );
    } else if (isInserting) {
      context.missing(_sexeMeta);
    }
    if (data.containsKey('date_naissance')) {
      context.handle(
        _dateNaissanceMeta,
        dateNaissance.isAcceptableOrUnknown(
          data['date_naissance']!,
          _dateNaissanceMeta,
        ),
      );
    }
    if (data.containsKey('statut')) {
      context.handle(
        _statutMeta,
        statut.isAcceptableOrUnknown(data['statut']!, _statutMeta),
      );
    }
    if (data.containsKey('description')) {
      context.handle(
        _descriptionMeta,
        description.isAcceptableOrUnknown(
          data['description']!,
          _descriptionMeta,
        ),
      );
    }
    if (data.containsKey('sync_statut')) {
      context.handle(
        _syncStatutMeta,
        syncStatut.isAcceptableOrUnknown(data['sync_statut']!, _syncStatutMeta),
      );
    }
    if (data.containsKey('cree_le')) {
      context.handle(
        _creeLeMeta,
        creeLe.isAcceptableOrUnknown(data['cree_le']!, _creeLeMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  AnimauxData map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return AnimauxData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      truTraceId: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}tru_trace_id'],
      ),
      espece: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}espece'],
      )!,
      race: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}race'],
      ),
      sexe: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}sexe'],
      )!,
      dateNaissance: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}date_naissance'],
      ),
      statut: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}statut'],
      )!,
      description: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}description'],
      ),
      syncStatut: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}sync_statut'],
      )!,
      creeLe: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}cree_le'],
      )!,
    );
  }

  @override
  $AnimauxTable createAlias(String alias) {
    return $AnimauxTable(attachedDatabase, alias);
  }
}

class AnimauxData extends DataClass implements Insertable<AnimauxData> {
  final String id;
  final String? truTraceId;
  final String espece;
  final String? race;
  final String sexe;
  final DateTime? dateNaissance;
  final String statut;
  final String? description;
  final String syncStatut;
  final DateTime creeLe;
  const AnimauxData({
    required this.id,
    this.truTraceId,
    required this.espece,
    this.race,
    required this.sexe,
    this.dateNaissance,
    required this.statut,
    this.description,
    required this.syncStatut,
    required this.creeLe,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    if (!nullToAbsent || truTraceId != null) {
      map['tru_trace_id'] = Variable<String>(truTraceId);
    }
    map['espece'] = Variable<String>(espece);
    if (!nullToAbsent || race != null) {
      map['race'] = Variable<String>(race);
    }
    map['sexe'] = Variable<String>(sexe);
    if (!nullToAbsent || dateNaissance != null) {
      map['date_naissance'] = Variable<DateTime>(dateNaissance);
    }
    map['statut'] = Variable<String>(statut);
    if (!nullToAbsent || description != null) {
      map['description'] = Variable<String>(description);
    }
    map['sync_statut'] = Variable<String>(syncStatut);
    map['cree_le'] = Variable<DateTime>(creeLe);
    return map;
  }

  AnimauxCompanion toCompanion(bool nullToAbsent) {
    return AnimauxCompanion(
      id: Value(id),
      truTraceId: truTraceId == null && nullToAbsent
          ? const Value.absent()
          : Value(truTraceId),
      espece: Value(espece),
      race: race == null && nullToAbsent ? const Value.absent() : Value(race),
      sexe: Value(sexe),
      dateNaissance: dateNaissance == null && nullToAbsent
          ? const Value.absent()
          : Value(dateNaissance),
      statut: Value(statut),
      description: description == null && nullToAbsent
          ? const Value.absent()
          : Value(description),
      syncStatut: Value(syncStatut),
      creeLe: Value(creeLe),
    );
  }

  factory AnimauxData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return AnimauxData(
      id: serializer.fromJson<String>(json['id']),
      truTraceId: serializer.fromJson<String?>(json['truTraceId']),
      espece: serializer.fromJson<String>(json['espece']),
      race: serializer.fromJson<String?>(json['race']),
      sexe: serializer.fromJson<String>(json['sexe']),
      dateNaissance: serializer.fromJson<DateTime?>(json['dateNaissance']),
      statut: serializer.fromJson<String>(json['statut']),
      description: serializer.fromJson<String?>(json['description']),
      syncStatut: serializer.fromJson<String>(json['syncStatut']),
      creeLe: serializer.fromJson<DateTime>(json['creeLe']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'truTraceId': serializer.toJson<String?>(truTraceId),
      'espece': serializer.toJson<String>(espece),
      'race': serializer.toJson<String?>(race),
      'sexe': serializer.toJson<String>(sexe),
      'dateNaissance': serializer.toJson<DateTime?>(dateNaissance),
      'statut': serializer.toJson<String>(statut),
      'description': serializer.toJson<String?>(description),
      'syncStatut': serializer.toJson<String>(syncStatut),
      'creeLe': serializer.toJson<DateTime>(creeLe),
    };
  }

  AnimauxData copyWith({
    String? id,
    Value<String?> truTraceId = const Value.absent(),
    String? espece,
    Value<String?> race = const Value.absent(),
    String? sexe,
    Value<DateTime?> dateNaissance = const Value.absent(),
    String? statut,
    Value<String?> description = const Value.absent(),
    String? syncStatut,
    DateTime? creeLe,
  }) => AnimauxData(
    id: id ?? this.id,
    truTraceId: truTraceId.present ? truTraceId.value : this.truTraceId,
    espece: espece ?? this.espece,
    race: race.present ? race.value : this.race,
    sexe: sexe ?? this.sexe,
    dateNaissance: dateNaissance.present
        ? dateNaissance.value
        : this.dateNaissance,
    statut: statut ?? this.statut,
    description: description.present ? description.value : this.description,
    syncStatut: syncStatut ?? this.syncStatut,
    creeLe: creeLe ?? this.creeLe,
  );
  AnimauxData copyWithCompanion(AnimauxCompanion data) {
    return AnimauxData(
      id: data.id.present ? data.id.value : this.id,
      truTraceId: data.truTraceId.present
          ? data.truTraceId.value
          : this.truTraceId,
      espece: data.espece.present ? data.espece.value : this.espece,
      race: data.race.present ? data.race.value : this.race,
      sexe: data.sexe.present ? data.sexe.value : this.sexe,
      dateNaissance: data.dateNaissance.present
          ? data.dateNaissance.value
          : this.dateNaissance,
      statut: data.statut.present ? data.statut.value : this.statut,
      description: data.description.present
          ? data.description.value
          : this.description,
      syncStatut: data.syncStatut.present
          ? data.syncStatut.value
          : this.syncStatut,
      creeLe: data.creeLe.present ? data.creeLe.value : this.creeLe,
    );
  }

  @override
  String toString() {
    return (StringBuffer('AnimauxData(')
          ..write('id: $id, ')
          ..write('truTraceId: $truTraceId, ')
          ..write('espece: $espece, ')
          ..write('race: $race, ')
          ..write('sexe: $sexe, ')
          ..write('dateNaissance: $dateNaissance, ')
          ..write('statut: $statut, ')
          ..write('description: $description, ')
          ..write('syncStatut: $syncStatut, ')
          ..write('creeLe: $creeLe')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    truTraceId,
    espece,
    race,
    sexe,
    dateNaissance,
    statut,
    description,
    syncStatut,
    creeLe,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is AnimauxData &&
          other.id == this.id &&
          other.truTraceId == this.truTraceId &&
          other.espece == this.espece &&
          other.race == this.race &&
          other.sexe == this.sexe &&
          other.dateNaissance == this.dateNaissance &&
          other.statut == this.statut &&
          other.description == this.description &&
          other.syncStatut == this.syncStatut &&
          other.creeLe == this.creeLe);
}

class AnimauxCompanion extends UpdateCompanion<AnimauxData> {
  final Value<String> id;
  final Value<String?> truTraceId;
  final Value<String> espece;
  final Value<String?> race;
  final Value<String> sexe;
  final Value<DateTime?> dateNaissance;
  final Value<String> statut;
  final Value<String?> description;
  final Value<String> syncStatut;
  final Value<DateTime> creeLe;
  final Value<int> rowid;
  const AnimauxCompanion({
    this.id = const Value.absent(),
    this.truTraceId = const Value.absent(),
    this.espece = const Value.absent(),
    this.race = const Value.absent(),
    this.sexe = const Value.absent(),
    this.dateNaissance = const Value.absent(),
    this.statut = const Value.absent(),
    this.description = const Value.absent(),
    this.syncStatut = const Value.absent(),
    this.creeLe = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  AnimauxCompanion.insert({
    required String id,
    this.truTraceId = const Value.absent(),
    required String espece,
    this.race = const Value.absent(),
    required String sexe,
    this.dateNaissance = const Value.absent(),
    this.statut = const Value.absent(),
    this.description = const Value.absent(),
    this.syncStatut = const Value.absent(),
    this.creeLe = const Value.absent(),
    this.rowid = const Value.absent(),
  }) : id = Value(id),
       espece = Value(espece),
       sexe = Value(sexe);
  static Insertable<AnimauxData> custom({
    Expression<String>? id,
    Expression<String>? truTraceId,
    Expression<String>? espece,
    Expression<String>? race,
    Expression<String>? sexe,
    Expression<DateTime>? dateNaissance,
    Expression<String>? statut,
    Expression<String>? description,
    Expression<String>? syncStatut,
    Expression<DateTime>? creeLe,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (truTraceId != null) 'tru_trace_id': truTraceId,
      if (espece != null) 'espece': espece,
      if (race != null) 'race': race,
      if (sexe != null) 'sexe': sexe,
      if (dateNaissance != null) 'date_naissance': dateNaissance,
      if (statut != null) 'statut': statut,
      if (description != null) 'description': description,
      if (syncStatut != null) 'sync_statut': syncStatut,
      if (creeLe != null) 'cree_le': creeLe,
      if (rowid != null) 'rowid': rowid,
    });
  }

  AnimauxCompanion copyWith({
    Value<String>? id,
    Value<String?>? truTraceId,
    Value<String>? espece,
    Value<String?>? race,
    Value<String>? sexe,
    Value<DateTime?>? dateNaissance,
    Value<String>? statut,
    Value<String?>? description,
    Value<String>? syncStatut,
    Value<DateTime>? creeLe,
    Value<int>? rowid,
  }) {
    return AnimauxCompanion(
      id: id ?? this.id,
      truTraceId: truTraceId ?? this.truTraceId,
      espece: espece ?? this.espece,
      race: race ?? this.race,
      sexe: sexe ?? this.sexe,
      dateNaissance: dateNaissance ?? this.dateNaissance,
      statut: statut ?? this.statut,
      description: description ?? this.description,
      syncStatut: syncStatut ?? this.syncStatut,
      creeLe: creeLe ?? this.creeLe,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (truTraceId.present) {
      map['tru_trace_id'] = Variable<String>(truTraceId.value);
    }
    if (espece.present) {
      map['espece'] = Variable<String>(espece.value);
    }
    if (race.present) {
      map['race'] = Variable<String>(race.value);
    }
    if (sexe.present) {
      map['sexe'] = Variable<String>(sexe.value);
    }
    if (dateNaissance.present) {
      map['date_naissance'] = Variable<DateTime>(dateNaissance.value);
    }
    if (statut.present) {
      map['statut'] = Variable<String>(statut.value);
    }
    if (description.present) {
      map['description'] = Variable<String>(description.value);
    }
    if (syncStatut.present) {
      map['sync_statut'] = Variable<String>(syncStatut.value);
    }
    if (creeLe.present) {
      map['cree_le'] = Variable<DateTime>(creeLe.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('AnimauxCompanion(')
          ..write('id: $id, ')
          ..write('truTraceId: $truTraceId, ')
          ..write('espece: $espece, ')
          ..write('race: $race, ')
          ..write('sexe: $sexe, ')
          ..write('dateNaissance: $dateNaissance, ')
          ..write('statut: $statut, ')
          ..write('description: $description, ')
          ..write('syncStatut: $syncStatut, ')
          ..write('creeLe: $creeLe, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $SyncQueueTable extends SyncQueue
    with TableInfo<$SyncQueueTable, SyncQueueData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $SyncQueueTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _entityTypeMeta = const VerificationMeta(
    'entityType',
  );
  @override
  late final GeneratedColumn<String> entityType = GeneratedColumn<String>(
    'entity_type',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _entityIdMeta = const VerificationMeta(
    'entityId',
  );
  @override
  late final GeneratedColumn<String> entityId = GeneratedColumn<String>(
    'entity_id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _operationMeta = const VerificationMeta(
    'operation',
  );
  @override
  late final GeneratedColumn<String> operation = GeneratedColumn<String>(
    'operation',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _payloadJsonMeta = const VerificationMeta(
    'payloadJson',
  );
  @override
  late final GeneratedColumn<String> payloadJson = GeneratedColumn<String>(
    'payload_json',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _occurredAtMeta = const VerificationMeta(
    'occurredAt',
  );
  @override
  late final GeneratedColumn<DateTime> occurredAt = GeneratedColumn<DateTime>(
    'occurred_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _statutMeta = const VerificationMeta('statut');
  @override
  late final GeneratedColumn<String> statut = GeneratedColumn<String>(
    'statut',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('en_attente'),
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    entityType,
    entityId,
    operation,
    payloadJson,
    occurredAt,
    statut,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'sync_queue';
  @override
  VerificationContext validateIntegrity(
    Insertable<SyncQueueData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('entity_type')) {
      context.handle(
        _entityTypeMeta,
        entityType.isAcceptableOrUnknown(data['entity_type']!, _entityTypeMeta),
      );
    } else if (isInserting) {
      context.missing(_entityTypeMeta);
    }
    if (data.containsKey('entity_id')) {
      context.handle(
        _entityIdMeta,
        entityId.isAcceptableOrUnknown(data['entity_id']!, _entityIdMeta),
      );
    } else if (isInserting) {
      context.missing(_entityIdMeta);
    }
    if (data.containsKey('operation')) {
      context.handle(
        _operationMeta,
        operation.isAcceptableOrUnknown(data['operation']!, _operationMeta),
      );
    } else if (isInserting) {
      context.missing(_operationMeta);
    }
    if (data.containsKey('payload_json')) {
      context.handle(
        _payloadJsonMeta,
        payloadJson.isAcceptableOrUnknown(
          data['payload_json']!,
          _payloadJsonMeta,
        ),
      );
    } else if (isInserting) {
      context.missing(_payloadJsonMeta);
    }
    if (data.containsKey('occurred_at')) {
      context.handle(
        _occurredAtMeta,
        occurredAt.isAcceptableOrUnknown(data['occurred_at']!, _occurredAtMeta),
      );
    } else if (isInserting) {
      context.missing(_occurredAtMeta);
    }
    if (data.containsKey('statut')) {
      context.handle(
        _statutMeta,
        statut.isAcceptableOrUnknown(data['statut']!, _statutMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  SyncQueueData map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return SyncQueueData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      entityType: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}entity_type'],
      )!,
      entityId: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}entity_id'],
      )!,
      operation: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}operation'],
      )!,
      payloadJson: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}payload_json'],
      )!,
      occurredAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}occurred_at'],
      )!,
      statut: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}statut'],
      )!,
    );
  }

  @override
  $SyncQueueTable createAlias(String alias) {
    return $SyncQueueTable(attachedDatabase, alias);
  }
}

class SyncQueueData extends DataClass implements Insertable<SyncQueueData> {
  final String id;
  final String entityType;
  final String entityId;
  final String operation;
  final String payloadJson;
  final DateTime occurredAt;
  final String statut;
  const SyncQueueData({
    required this.id,
    required this.entityType,
    required this.entityId,
    required this.operation,
    required this.payloadJson,
    required this.occurredAt,
    required this.statut,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    map['entity_type'] = Variable<String>(entityType);
    map['entity_id'] = Variable<String>(entityId);
    map['operation'] = Variable<String>(operation);
    map['payload_json'] = Variable<String>(payloadJson);
    map['occurred_at'] = Variable<DateTime>(occurredAt);
    map['statut'] = Variable<String>(statut);
    return map;
  }

  SyncQueueCompanion toCompanion(bool nullToAbsent) {
    return SyncQueueCompanion(
      id: Value(id),
      entityType: Value(entityType),
      entityId: Value(entityId),
      operation: Value(operation),
      payloadJson: Value(payloadJson),
      occurredAt: Value(occurredAt),
      statut: Value(statut),
    );
  }

  factory SyncQueueData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return SyncQueueData(
      id: serializer.fromJson<String>(json['id']),
      entityType: serializer.fromJson<String>(json['entityType']),
      entityId: serializer.fromJson<String>(json['entityId']),
      operation: serializer.fromJson<String>(json['operation']),
      payloadJson: serializer.fromJson<String>(json['payloadJson']),
      occurredAt: serializer.fromJson<DateTime>(json['occurredAt']),
      statut: serializer.fromJson<String>(json['statut']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'entityType': serializer.toJson<String>(entityType),
      'entityId': serializer.toJson<String>(entityId),
      'operation': serializer.toJson<String>(operation),
      'payloadJson': serializer.toJson<String>(payloadJson),
      'occurredAt': serializer.toJson<DateTime>(occurredAt),
      'statut': serializer.toJson<String>(statut),
    };
  }

  SyncQueueData copyWith({
    String? id,
    String? entityType,
    String? entityId,
    String? operation,
    String? payloadJson,
    DateTime? occurredAt,
    String? statut,
  }) => SyncQueueData(
    id: id ?? this.id,
    entityType: entityType ?? this.entityType,
    entityId: entityId ?? this.entityId,
    operation: operation ?? this.operation,
    payloadJson: payloadJson ?? this.payloadJson,
    occurredAt: occurredAt ?? this.occurredAt,
    statut: statut ?? this.statut,
  );
  SyncQueueData copyWithCompanion(SyncQueueCompanion data) {
    return SyncQueueData(
      id: data.id.present ? data.id.value : this.id,
      entityType: data.entityType.present
          ? data.entityType.value
          : this.entityType,
      entityId: data.entityId.present ? data.entityId.value : this.entityId,
      operation: data.operation.present ? data.operation.value : this.operation,
      payloadJson: data.payloadJson.present
          ? data.payloadJson.value
          : this.payloadJson,
      occurredAt: data.occurredAt.present
          ? data.occurredAt.value
          : this.occurredAt,
      statut: data.statut.present ? data.statut.value : this.statut,
    );
  }

  @override
  String toString() {
    return (StringBuffer('SyncQueueData(')
          ..write('id: $id, ')
          ..write('entityType: $entityType, ')
          ..write('entityId: $entityId, ')
          ..write('operation: $operation, ')
          ..write('payloadJson: $payloadJson, ')
          ..write('occurredAt: $occurredAt, ')
          ..write('statut: $statut')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    entityType,
    entityId,
    operation,
    payloadJson,
    occurredAt,
    statut,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is SyncQueueData &&
          other.id == this.id &&
          other.entityType == this.entityType &&
          other.entityId == this.entityId &&
          other.operation == this.operation &&
          other.payloadJson == this.payloadJson &&
          other.occurredAt == this.occurredAt &&
          other.statut == this.statut);
}

class SyncQueueCompanion extends UpdateCompanion<SyncQueueData> {
  final Value<String> id;
  final Value<String> entityType;
  final Value<String> entityId;
  final Value<String> operation;
  final Value<String> payloadJson;
  final Value<DateTime> occurredAt;
  final Value<String> statut;
  final Value<int> rowid;
  const SyncQueueCompanion({
    this.id = const Value.absent(),
    this.entityType = const Value.absent(),
    this.entityId = const Value.absent(),
    this.operation = const Value.absent(),
    this.payloadJson = const Value.absent(),
    this.occurredAt = const Value.absent(),
    this.statut = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  SyncQueueCompanion.insert({
    required String id,
    required String entityType,
    required String entityId,
    required String operation,
    required String payloadJson,
    required DateTime occurredAt,
    this.statut = const Value.absent(),
    this.rowid = const Value.absent(),
  }) : id = Value(id),
       entityType = Value(entityType),
       entityId = Value(entityId),
       operation = Value(operation),
       payloadJson = Value(payloadJson),
       occurredAt = Value(occurredAt);
  static Insertable<SyncQueueData> custom({
    Expression<String>? id,
    Expression<String>? entityType,
    Expression<String>? entityId,
    Expression<String>? operation,
    Expression<String>? payloadJson,
    Expression<DateTime>? occurredAt,
    Expression<String>? statut,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (entityType != null) 'entity_type': entityType,
      if (entityId != null) 'entity_id': entityId,
      if (operation != null) 'operation': operation,
      if (payloadJson != null) 'payload_json': payloadJson,
      if (occurredAt != null) 'occurred_at': occurredAt,
      if (statut != null) 'statut': statut,
      if (rowid != null) 'rowid': rowid,
    });
  }

  SyncQueueCompanion copyWith({
    Value<String>? id,
    Value<String>? entityType,
    Value<String>? entityId,
    Value<String>? operation,
    Value<String>? payloadJson,
    Value<DateTime>? occurredAt,
    Value<String>? statut,
    Value<int>? rowid,
  }) {
    return SyncQueueCompanion(
      id: id ?? this.id,
      entityType: entityType ?? this.entityType,
      entityId: entityId ?? this.entityId,
      operation: operation ?? this.operation,
      payloadJson: payloadJson ?? this.payloadJson,
      occurredAt: occurredAt ?? this.occurredAt,
      statut: statut ?? this.statut,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (entityType.present) {
      map['entity_type'] = Variable<String>(entityType.value);
    }
    if (entityId.present) {
      map['entity_id'] = Variable<String>(entityId.value);
    }
    if (operation.present) {
      map['operation'] = Variable<String>(operation.value);
    }
    if (payloadJson.present) {
      map['payload_json'] = Variable<String>(payloadJson.value);
    }
    if (occurredAt.present) {
      map['occurred_at'] = Variable<DateTime>(occurredAt.value);
    }
    if (statut.present) {
      map['statut'] = Variable<String>(statut.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('SyncQueueCompanion(')
          ..write('id: $id, ')
          ..write('entityType: $entityType, ')
          ..write('entityId: $entityId, ')
          ..write('operation: $operation, ')
          ..write('payloadJson: $payloadJson, ')
          ..write('occurredAt: $occurredAt, ')
          ..write('statut: $statut, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

abstract class _$AppDatabase extends GeneratedDatabase {
  _$AppDatabase(QueryExecutor e) : super(e);
  $AppDatabaseManager get managers => $AppDatabaseManager(this);
  late final $AnimauxTable animaux = $AnimauxTable(this);
  late final $SyncQueueTable syncQueue = $SyncQueueTable(this);
  @override
  Iterable<TableInfo<Table, Object?>> get allTables =>
      allSchemaEntities.whereType<TableInfo<Table, Object?>>();
  @override
  List<DatabaseSchemaEntity> get allSchemaEntities => [animaux, syncQueue];
}

typedef $$AnimauxTableCreateCompanionBuilder =
    AnimauxCompanion Function({
      required String id,
      Value<String?> truTraceId,
      required String espece,
      Value<String?> race,
      required String sexe,
      Value<DateTime?> dateNaissance,
      Value<String> statut,
      Value<String?> description,
      Value<String> syncStatut,
      Value<DateTime> creeLe,
      Value<int> rowid,
    });
typedef $$AnimauxTableUpdateCompanionBuilder =
    AnimauxCompanion Function({
      Value<String> id,
      Value<String?> truTraceId,
      Value<String> espece,
      Value<String?> race,
      Value<String> sexe,
      Value<DateTime?> dateNaissance,
      Value<String> statut,
      Value<String?> description,
      Value<String> syncStatut,
      Value<DateTime> creeLe,
      Value<int> rowid,
    });

class $$AnimauxTableFilterComposer
    extends Composer<_$AppDatabase, $AnimauxTable> {
  $$AnimauxTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get truTraceId => $composableBuilder(
    column: $table.truTraceId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get espece => $composableBuilder(
    column: $table.espece,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get race => $composableBuilder(
    column: $table.race,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get sexe => $composableBuilder(
    column: $table.sexe,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get dateNaissance => $composableBuilder(
    column: $table.dateNaissance,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get statut => $composableBuilder(
    column: $table.statut,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get description => $composableBuilder(
    column: $table.description,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get syncStatut => $composableBuilder(
    column: $table.syncStatut,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get creeLe => $composableBuilder(
    column: $table.creeLe,
    builder: (column) => ColumnFilters(column),
  );
}

class $$AnimauxTableOrderingComposer
    extends Composer<_$AppDatabase, $AnimauxTable> {
  $$AnimauxTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get truTraceId => $composableBuilder(
    column: $table.truTraceId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get espece => $composableBuilder(
    column: $table.espece,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get race => $composableBuilder(
    column: $table.race,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get sexe => $composableBuilder(
    column: $table.sexe,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get dateNaissance => $composableBuilder(
    column: $table.dateNaissance,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get statut => $composableBuilder(
    column: $table.statut,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get description => $composableBuilder(
    column: $table.description,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get syncStatut => $composableBuilder(
    column: $table.syncStatut,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get creeLe => $composableBuilder(
    column: $table.creeLe,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$AnimauxTableAnnotationComposer
    extends Composer<_$AppDatabase, $AnimauxTable> {
  $$AnimauxTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get truTraceId => $composableBuilder(
    column: $table.truTraceId,
    builder: (column) => column,
  );

  GeneratedColumn<String> get espece =>
      $composableBuilder(column: $table.espece, builder: (column) => column);

  GeneratedColumn<String> get race =>
      $composableBuilder(column: $table.race, builder: (column) => column);

  GeneratedColumn<String> get sexe =>
      $composableBuilder(column: $table.sexe, builder: (column) => column);

  GeneratedColumn<DateTime> get dateNaissance => $composableBuilder(
    column: $table.dateNaissance,
    builder: (column) => column,
  );

  GeneratedColumn<String> get statut =>
      $composableBuilder(column: $table.statut, builder: (column) => column);

  GeneratedColumn<String> get description => $composableBuilder(
    column: $table.description,
    builder: (column) => column,
  );

  GeneratedColumn<String> get syncStatut => $composableBuilder(
    column: $table.syncStatut,
    builder: (column) => column,
  );

  GeneratedColumn<DateTime> get creeLe =>
      $composableBuilder(column: $table.creeLe, builder: (column) => column);
}

class $$AnimauxTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $AnimauxTable,
          AnimauxData,
          $$AnimauxTableFilterComposer,
          $$AnimauxTableOrderingComposer,
          $$AnimauxTableAnnotationComposer,
          $$AnimauxTableCreateCompanionBuilder,
          $$AnimauxTableUpdateCompanionBuilder,
          (
            AnimauxData,
            BaseReferences<_$AppDatabase, $AnimauxTable, AnimauxData>,
          ),
          AnimauxData,
          PrefetchHooks Function()
        > {
  $$AnimauxTableTableManager(_$AppDatabase db, $AnimauxTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$AnimauxTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$AnimauxTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$AnimauxTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String?> truTraceId = const Value.absent(),
                Value<String> espece = const Value.absent(),
                Value<String?> race = const Value.absent(),
                Value<String> sexe = const Value.absent(),
                Value<DateTime?> dateNaissance = const Value.absent(),
                Value<String> statut = const Value.absent(),
                Value<String?> description = const Value.absent(),
                Value<String> syncStatut = const Value.absent(),
                Value<DateTime> creeLe = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => AnimauxCompanion(
                id: id,
                truTraceId: truTraceId,
                espece: espece,
                race: race,
                sexe: sexe,
                dateNaissance: dateNaissance,
                statut: statut,
                description: description,
                syncStatut: syncStatut,
                creeLe: creeLe,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                Value<String?> truTraceId = const Value.absent(),
                required String espece,
                Value<String?> race = const Value.absent(),
                required String sexe,
                Value<DateTime?> dateNaissance = const Value.absent(),
                Value<String> statut = const Value.absent(),
                Value<String?> description = const Value.absent(),
                Value<String> syncStatut = const Value.absent(),
                Value<DateTime> creeLe = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => AnimauxCompanion.insert(
                id: id,
                truTraceId: truTraceId,
                espece: espece,
                race: race,
                sexe: sexe,
                dateNaissance: dateNaissance,
                statut: statut,
                description: description,
                syncStatut: syncStatut,
                creeLe: creeLe,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$AnimauxTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $AnimauxTable,
      AnimauxData,
      $$AnimauxTableFilterComposer,
      $$AnimauxTableOrderingComposer,
      $$AnimauxTableAnnotationComposer,
      $$AnimauxTableCreateCompanionBuilder,
      $$AnimauxTableUpdateCompanionBuilder,
      (AnimauxData, BaseReferences<_$AppDatabase, $AnimauxTable, AnimauxData>),
      AnimauxData,
      PrefetchHooks Function()
    >;
typedef $$SyncQueueTableCreateCompanionBuilder =
    SyncQueueCompanion Function({
      required String id,
      required String entityType,
      required String entityId,
      required String operation,
      required String payloadJson,
      required DateTime occurredAt,
      Value<String> statut,
      Value<int> rowid,
    });
typedef $$SyncQueueTableUpdateCompanionBuilder =
    SyncQueueCompanion Function({
      Value<String> id,
      Value<String> entityType,
      Value<String> entityId,
      Value<String> operation,
      Value<String> payloadJson,
      Value<DateTime> occurredAt,
      Value<String> statut,
      Value<int> rowid,
    });

class $$SyncQueueTableFilterComposer
    extends Composer<_$AppDatabase, $SyncQueueTable> {
  $$SyncQueueTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get entityType => $composableBuilder(
    column: $table.entityType,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get entityId => $composableBuilder(
    column: $table.entityId,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get operation => $composableBuilder(
    column: $table.operation,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get payloadJson => $composableBuilder(
    column: $table.payloadJson,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get occurredAt => $composableBuilder(
    column: $table.occurredAt,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get statut => $composableBuilder(
    column: $table.statut,
    builder: (column) => ColumnFilters(column),
  );
}

class $$SyncQueueTableOrderingComposer
    extends Composer<_$AppDatabase, $SyncQueueTable> {
  $$SyncQueueTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get entityType => $composableBuilder(
    column: $table.entityType,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get entityId => $composableBuilder(
    column: $table.entityId,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get operation => $composableBuilder(
    column: $table.operation,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get payloadJson => $composableBuilder(
    column: $table.payloadJson,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get occurredAt => $composableBuilder(
    column: $table.occurredAt,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get statut => $composableBuilder(
    column: $table.statut,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$SyncQueueTableAnnotationComposer
    extends Composer<_$AppDatabase, $SyncQueueTable> {
  $$SyncQueueTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get entityType => $composableBuilder(
    column: $table.entityType,
    builder: (column) => column,
  );

  GeneratedColumn<String> get entityId =>
      $composableBuilder(column: $table.entityId, builder: (column) => column);

  GeneratedColumn<String> get operation =>
      $composableBuilder(column: $table.operation, builder: (column) => column);

  GeneratedColumn<String> get payloadJson => $composableBuilder(
    column: $table.payloadJson,
    builder: (column) => column,
  );

  GeneratedColumn<DateTime> get occurredAt => $composableBuilder(
    column: $table.occurredAt,
    builder: (column) => column,
  );

  GeneratedColumn<String> get statut =>
      $composableBuilder(column: $table.statut, builder: (column) => column);
}

class $$SyncQueueTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $SyncQueueTable,
          SyncQueueData,
          $$SyncQueueTableFilterComposer,
          $$SyncQueueTableOrderingComposer,
          $$SyncQueueTableAnnotationComposer,
          $$SyncQueueTableCreateCompanionBuilder,
          $$SyncQueueTableUpdateCompanionBuilder,
          (
            SyncQueueData,
            BaseReferences<_$AppDatabase, $SyncQueueTable, SyncQueueData>,
          ),
          SyncQueueData,
          PrefetchHooks Function()
        > {
  $$SyncQueueTableTableManager(_$AppDatabase db, $SyncQueueTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$SyncQueueTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$SyncQueueTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$SyncQueueTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String> entityType = const Value.absent(),
                Value<String> entityId = const Value.absent(),
                Value<String> operation = const Value.absent(),
                Value<String> payloadJson = const Value.absent(),
                Value<DateTime> occurredAt = const Value.absent(),
                Value<String> statut = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => SyncQueueCompanion(
                id: id,
                entityType: entityType,
                entityId: entityId,
                operation: operation,
                payloadJson: payloadJson,
                occurredAt: occurredAt,
                statut: statut,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                required String entityType,
                required String entityId,
                required String operation,
                required String payloadJson,
                required DateTime occurredAt,
                Value<String> statut = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => SyncQueueCompanion.insert(
                id: id,
                entityType: entityType,
                entityId: entityId,
                operation: operation,
                payloadJson: payloadJson,
                occurredAt: occurredAt,
                statut: statut,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$SyncQueueTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $SyncQueueTable,
      SyncQueueData,
      $$SyncQueueTableFilterComposer,
      $$SyncQueueTableOrderingComposer,
      $$SyncQueueTableAnnotationComposer,
      $$SyncQueueTableCreateCompanionBuilder,
      $$SyncQueueTableUpdateCompanionBuilder,
      (
        SyncQueueData,
        BaseReferences<_$AppDatabase, $SyncQueueTable, SyncQueueData>,
      ),
      SyncQueueData,
      PrefetchHooks Function()
    >;

class $AppDatabaseManager {
  final _$AppDatabase _db;
  $AppDatabaseManager(this._db);
  $$AnimauxTableTableManager get animaux =>
      $$AnimauxTableTableManager(_db, _db.animaux);
  $$SyncQueueTableTableManager get syncQueue =>
      $$SyncQueueTableTableManager(_db, _db.syncQueue);
}
