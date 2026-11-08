import 'package:equatable/equatable.dart';

enum ResidentRole {
  proprietario,
  inquilino,
  morador;

  String get label {
    switch (this) {
      case ResidentRole.proprietario:
        return 'Proprietário';
      case ResidentRole.inquilino:
        return 'Inquilino';
      case ResidentRole.morador:
        return 'Morador';
    }
  }

  static ResidentRole fromString(String? value) {
    switch (value?.toLowerCase()) {
      case 'proprietario':
        return ResidentRole.proprietario;
      case 'inquilino':
        return ResidentRole.inquilino;
      default:
        return ResidentRole.morador;
    }
  }
}

/// Modelo de Unidade / Imóvel do Usuário
class UnitModel extends Equatable {
  final int apartmentId;
  final String unitNumber;
  final String blockOrTower;
  final String condominiumName;
  final ResidentRole role;
  final bool isDefault;

  const UnitModel({
    required this.apartmentId,
    required this.unitNumber,
    required this.blockOrTower,
    required this.condominiumName,
    required this.role,
    this.isDefault = false,
  });

  String get displayName => 'Apto $unitNumber • $blockOrTower';

  factory UnitModel.fromJson(Map<String, dynamic> json) {
    return UnitModel(
      apartmentId: json['apartment_id'] as int,
      unitNumber: json['unit_number'] as String? ?? '',
      blockOrTower: json['block_or_tower'] as String? ?? '',
      condominiumName: json['condominium_name'] as String? ?? 'CondoAI',
      role: ResidentRole.fromString(json['classification'] as String?),
      isDefault: json['is_default'] as bool? ?? false,
    );
  }

  @override
  List<Object?> get props => [
        apartmentId,
        unitNumber,
        blockOrTower,
        condominiumName,
        role,
        isDefault,
      ];
}
