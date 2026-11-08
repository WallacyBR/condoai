import 'package:equatable/equatable.dart';
import '../../../data/models/unit_model.dart';

abstract class UnitState extends Equatable {
  const UnitState();

  @override
  List<Object?> get props => [];
}

class UnitInitial extends UnitState {}

class UnitLoaded extends UnitState {
  final List<UnitModel> availableUnits;
  final UnitModel activeUnit;

  const UnitLoaded({
    required this.availableUnits,
    required this.activeUnit,
  });

  ResidentRole get currentRole => activeUnit.role;

  UnitLoaded copyWith({
    List<UnitModel>? availableUnits,
    UnitModel? activeUnit,
  }) {
    return UnitLoaded(
      availableUnits: availableUnits ?? this.availableUnits,
      activeUnit: activeUnit ?? this.activeUnit,
    );
  }

  @override
  List<Object?> get props => [availableUnits, activeUnit];
}
