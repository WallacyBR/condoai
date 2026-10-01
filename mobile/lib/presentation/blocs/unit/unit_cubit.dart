import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/models/unit_model.dart';
import 'unit_state.dart';

/// Gerencia a unidade selecionada e as regras de negócio para Proprietário, Inquilino e Morador
class UnitCubit extends Cubit<UnitState> {
  UnitCubit() : super(UnitInitial()) {
    loadMockUnits();
  }

  void loadMockUnits() {
    final mockUnits = [
      const UnitModel(
        apartmentId: 101,
        unitNumber: '402',
        blockOrTower: 'Bloco Primavera',
        condominiumName: 'Residencial Jardins',
        role: ResidentRole.inquilino,
        isDefault: true,
      ),
      const UnitModel(
        apartmentId: 205,
        unitNumber: '1104',
        blockOrTower: 'Torre Magnólia',
        condominiumName: 'Condomínio Horizon',
        role: ResidentRole.proprietario,
      ),
    ];

    emit(UnitLoaded(
      availableUnits: mockUnits,
      activeUnit: mockUnits.first,
    ));
  }

  void switchUnit(UnitModel newUnit) {
    if (state is UnitLoaded) {
      final current = state as UnitLoaded;
      emit(current.copyWith(activeUnit: newUnit));
    }
  }
}
