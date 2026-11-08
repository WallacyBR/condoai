import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/models/access_request_model.dart';
import '../../../domain/entities/access_decision.dart';
import 'visitor_call_state.dart';

/// Controla o recebimento e resposta das chamadas de visitantes (Interfone Virtual Noknox)
class VisitorCallCubit extends Cubit<VisitorCallState> {
  VisitorCallCubit() : super(VisitorCallIdle());

  /// Simula a chegada de uma chamada de visitante da portaria
  void triggerMockCall() {
    final mockCall = AccessRequestModel(
      id: 994,
      visitorName: 'Carlos Mendes',
      visitorType: 'Entrega Encomenda',
      company: 'Mercado Livre',
      visitorDocument: '384.920.118-22',
      visitorPhotoUrl: null,
      vehiclePlate: 'BRA2E19',
      vehicleModel: 'Fiat Fiorino Branca',
      requestTime: DateTime.now(),
      status: 'pending',
      gatehouseObservation: 'Entregador na Portaria Principal com pacote volumoso',
    );

    emit(VisitorCallIncoming(mockCall));
  }

  /// Responde à chamada com uma das 4 opções solicitadas
  Future<void> answerCall(AccessDecision decision, {String? notes}) async {
    if (state is! VisitorCallIncoming) return;
    final currentCall = (state as VisitorCallIncoming).call;

    emit(VisitorCallProcessing(call: currentCall, decision: decision));

    // Simulação do tempo de resposta de rede para a API
    await Future.delayed(const Duration(milliseconds: 600));

    String feedbackMessage;
    switch (decision) {
      case AccessDecision.authorized:
        feedbackMessage = 'Entrada de ${currentCall.visitorName} autorizada!';
        break;
      case AccessDecision.awaitingAtGatehouse:
        feedbackMessage = 'Portaria avisada: visitante aguardará sua descida.';
        break;
      case AccessDecision.informNotHome:
        feedbackMessage = 'Portaria avisada que você não está no momento.';
        break;
      case AccessDecision.unauthorized:
        feedbackMessage = 'Entrada recusada.';
        break;
    }

    emit(VisitorCallFinished(decision: decision, message: feedbackMessage));

    // Retorna ao estado ocioso após 2 segundos
    await Future.delayed(const Duration(seconds: 2));
    emit(VisitorCallIdle());
  }

  void dismissCall() {
    emit(VisitorCallIdle());
  }
}
