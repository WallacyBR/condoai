import 'package:flutter_test/flutter_test.dart';
import 'package:condoai_app/presentation/app.dart';
import 'package:condoai_app/presentation/blocs/unit/unit_cubit.dart';
import 'package:condoai_app/presentation/blocs/unit/unit_state.dart';
import 'package:condoai_app/presentation/blocs/visitor_call/visitor_call_cubit.dart';
import 'package:condoai_app/presentation/blocs/visitor_call/visitor_call_state.dart';
import 'package:condoai_app/domain/entities/access_decision.dart';
import 'package:condoai_app/data/models/unit_model.dart';

void main() {
  group('UnitCubit Tests', () {
    test('carrega unidades iniciais com sucesso', () {
      final cubit = UnitCubit();
      expect(cubit.state, isA<UnitLoaded>());
      final state = cubit.state as UnitLoaded;
      expect(state.availableUnits.length, greaterThanOrEqualTo(2));
      expect(state.activeUnit.role, equals(ResidentRole.inquilino));
    });

    test('permite alternar unidade ativa', () {
      final cubit = UnitCubit();
      final state = cubit.state as UnitLoaded;
      final secondUnit = state.availableUnits[1];

      cubit.switchUnit(secondUnit);

      final newState = cubit.state as UnitLoaded;
      expect(newState.activeUnit.apartmentId, equals(secondUnit.apartmentId));
      expect(newState.activeUnit.role, equals(ResidentRole.proprietario));
    });
  });

  group('VisitorCallCubit Tests (Noknox Intercom)', () {
    test('inicia no estado ocioso (VisitorCallIdle)', () {
      final cubit = VisitorCallCubit();
      expect(cubit.state, isA<VisitorCallIdle>());
    });

    test('dispara chamada com sucesso e permite responder autorizando', () async {
      final cubit = VisitorCallCubit();

      cubit.triggerMockCall();
      expect(cubit.state, isA<VisitorCallIncoming>());

      final incomingState = cubit.state as VisitorCallIncoming;
      expect(incomingState.call.visitorName, equals('Carlos Mendes'));

      final states = <VisitorCallState>[];
      final subscription = cubit.stream.listen(states.add);

      // Responde autorizando a entrada
      await cubit.answerCall(AccessDecision.authorized);

      expect(states.any((s) => s is VisitorCallProcessing), isTrue);
      expect(
        states.any((s) => s is VisitorCallFinished && s.decision == AccessDecision.authorized),
        isTrue,
      );
      expect(cubit.state, isA<VisitorCallIdle>());

      await subscription.cancel();
    });
  });

  group('PrediHubApp Widget Tests', () {
    testWidgets('renderiza o app com navegação inferior e título', (WidgetTester tester) async {
      await tester.pumpWidget(const PrediHubApp());
      await tester.pumpAndSettle();

      expect(find.text('Olá, Wallace'), findsOneWidget);
      expect(find.text('Início'), findsOneWidget);
      expect(find.text('Avisos'), findsOneWidget);
      expect(find.text('Reservas'), findsOneWidget);
      expect(find.text('Perfil'), findsOneWidget);
    });
  });
}
