import 'package:equatable/equatable.dart';
import '../../../data/models/access_request_model.dart';
import '../../../domain/entities/access_decision.dart';

abstract class VisitorCallState extends Equatable {
  const VisitorCallState();

  @override
  List<Object?> get props => [];
}

class VisitorCallIdle extends VisitorCallState {}

class VisitorCallIncoming extends VisitorCallState {
  final AccessRequestModel call;

  const VisitorCallIncoming(this.call);

  @override
  List<Object?> get props => [call];
}

class VisitorCallProcessing extends VisitorCallState {
  final AccessRequestModel call;
  final AccessDecision decision;

  const VisitorCallProcessing({
    required this.call,
    required this.decision,
  });

  @override
  List<Object?> get props => [call, decision];
}

class VisitorCallFinished extends VisitorCallState {
  final AccessDecision decision;
  final String message;

  const VisitorCallFinished({
    required this.decision,
    required this.message,
  });

  @override
  List<Object?> get props => [decision, message];
}
