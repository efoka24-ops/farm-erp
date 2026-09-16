import 'package:flutter_test/flutter_test.dart';

import 'package:farm_erp_mobile/main.dart';

void main() {
  testWidgets('affiche le tableau de bord au démarrage', (
    WidgetTester tester,
  ) async {
    await tester.pumpWidget(const TruFarmApp());
    await tester.pump();

    expect(find.text('Tableau de bord'), findsWidgets);
  });
}
