import 'package:flutter/material.dart';

import 'core/db/app_database.dart';
import 'core/sync/sync_queue_repository.dart';
import 'features/cheptel/animal_repository.dart';
import 'features/cheptel/fiche_animal_screen.dart';
import 'features/dashboard/dashboard_screen.dart';

void main() {
  runApp(const TruFarmApp());
}

class TruFarmApp extends StatefulWidget {
  const TruFarmApp({super.key});

  @override
  State<TruFarmApp> createState() => _TruFarmAppState();
}

class _TruFarmAppState extends State<TruFarmApp> {
  late final AppDatabase _db;
  late final AnimalRepository _animalRepository;

  @override
  void initState() {
    super.initState();
    _db = AppDatabase();
    _animalRepository = AnimalRepository(_db, SyncQueueRepository(_db));
  }

  @override
  void dispose() {
    _db.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'TRU FARM ERP',
      theme: ThemeData(colorSchemeSeed: Colors.green, useMaterial3: true),
      home: _AccueilTabs(db: _db, animalRepository: _animalRepository),
    );
  }
}

class _AccueilTabs extends StatefulWidget {
  const _AccueilTabs({required this.db, required this.animalRepository});

  final AppDatabase db;
  final AnimalRepository animalRepository;

  @override
  State<_AccueilTabs> createState() => _AccueilTabsState();
}

class _AccueilTabsState extends State<_AccueilTabs> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    final ecrans = [
      DashboardScreen(db: widget.db),
      FicheAnimalScreen(repository: widget.animalRepository),
    ];

    return Scaffold(
      body: ecrans[_index],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard),
            label: 'Tableau de bord',
          ),
          NavigationDestination(icon: Icon(Icons.pets), label: 'Cheptel'),
        ],
      ),
    );
  }
}
