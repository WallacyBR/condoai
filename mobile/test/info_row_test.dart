import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:condoai_app/core/widgets/info_row.dart';

void main() {
  testWidgets('InfoRow não estoura com textos longos', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SizedBox(
            width: 200,
            child: InfoRow(
              icon: Icons.info_outline,
              label: 'Destinatário muito longo para caber',
              value: 'Nome extremamente extenso do morador cadastrado',
              isDark: false,
            ),
          ),
        ),
      ),
    );

    expect(tester.takeException(), isNull);
    expect(find.byType(InfoRow), findsOneWidget);
  });
}
