import '../models/unit_model.dart';

/// Escopo de tenant para chaves SharedPreferences (condomínio + unidade).
///
/// PII local mock fica isolada por unidade até integração com API autenticada (P1).
class TenantScope {
  final int apartmentId;
  final String condominiumName;

  const TenantScope({
    required this.apartmentId,
    required this.condominiumName,
  });

  factory TenantScope.fromUnit(UnitModel unit) => TenantScope(
        apartmentId: unit.apartmentId,
        condominiumName: unit.condominiumName,
      );

  /// Chave estável para prefixar prefs desta unidade/condomínio.
  String get storageKey =>
      'condo_${condominiumName.replaceAll(RegExp(r'[^a-zA-Z0-9]'), '_').toLowerCase()}_apt_$apartmentId';
}
