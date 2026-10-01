import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/registry_models.dart';
import 'tenant_scope.dart';

/// Persistência local dos cadastros enquanto a API não está integrada.
///
/// PII (CPF, telefones, endereço) fica em SharedPreferences **escopado por
/// condomínio + unidade** — mock local apenas; substituir por API autenticada (P1).
class LocalRegistryStore {
  final SharedPreferences _prefs;
  final TenantScope scope;

  LocalRegistryStore(this._prefs, this.scope);

  String get _residentsKey => '${scope.storageKey}_registry_residents_v2';
  String get _petsKey => '${scope.storageKey}_registry_pets_v2';
  String get _vehiclesKey => '${scope.storageKey}_registry_vehicles_v2';
  String get _seededKey => '${scope.storageKey}_registry_seeded_v2';

  static Future<LocalRegistryStore> create({required TenantScope scope}) async {
    final prefs = await SharedPreferences.getInstance();
    final store = LocalRegistryStore(prefs, scope);
    await store._seedIfNeeded();
    return store;
  }

  Future<void> _seedIfNeeded() async {
    if (_prefs.getBool(_seededKey) == true) return;

    await saveResidents([
      ResidentRegistry(
        id: 'seed-1',
        name: 'Wallacy Silva',
        email: 'wallacy@condoai.com',
        cpf: '412.000.000-89',
        rg: '12.345.678-9',
        cellPhone: '(11) 98765-4321',
        commercialPhone: '(11) 3444-5555',
        whatsapp: '(11) 98765-4321',
        billingAddressType: 'condominium',
        classification: 'proprietario',
        relationship: 'Titular',
        isOwner: true,
      ),
      ResidentRegistry(
        id: 'seed-2',
        name: 'Larissa Silva',
        email: 'larissa@condoai.com',
        cpf: '389.000.000-20',
        cellPhone: '(11) 97654-3210',
        whatsapp: '(11) 97654-3210',
        classification: 'morador',
        relationship: 'Cônjuge',
      ),
    ]);

    await savePets([
      const PetRegistry(
        id: 'pet-1',
        name: 'Thor',
        species: 'Cão',
        breed: 'Golden Retriever',
        gender: 'Macho',
        age: '3 anos',
        weight: '32 kg',
        healthInfo: 'Vacinas V8/V10 e antirrábica em dia.',
      ),
    ]);

    await saveVehicles([
      const VehicleRegistry(
        id: 'veh-1',
        licensePlate: 'BRA-4X91',
        model: 'Jeep Compass Limited',
        color: 'Cinza Metálico',
        year: '2022',
        parkingSpot: 'Vaga G1 - 42 (Coberta)',
        notes: 'Tag LPR ativa na cancela de veículos.',
      ),
      const VehicleRegistry(
        id: 'veh-2',
        licensePlate: 'SP-9912',
        model: 'Honda CB 500F',
        color: 'Preta',
        year: '2020',
        parkingSpot: 'Vaga de Moto M-08',
      ),
    ]);

    await _prefs.setBool(_seededKey, true);
  }

  Future<List<ResidentRegistry>> loadResidents() async {
    final raw = _prefs.getString(_residentsKey);
    if (raw == null) return [];
    final list = jsonDecode(raw) as List<dynamic>;
    return list.map((e) => ResidentRegistry.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> saveResidents(List<ResidentRegistry> items) async {
    await _prefs.setString(
      _residentsKey,
      jsonEncode(items.map((e) => e.toJson()).toList()),
    );
  }

  Future<List<PetRegistry>> loadPets() async {
    final raw = _prefs.getString(_petsKey);
    if (raw == null) return [];
    final list = jsonDecode(raw) as List<dynamic>;
    return list.map((e) => PetRegistry.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> savePets(List<PetRegistry> items) async {
    await _prefs.setString(
      _petsKey,
      jsonEncode(items.map((e) => e.toJson()).toList()),
    );
  }

  Future<List<VehicleRegistry>> loadVehicles() async {
    final raw = _prefs.getString(_vehiclesKey);
    if (raw == null) return [];
    final list = jsonDecode(raw) as List<dynamic>;
    return list.map((e) => VehicleRegistry.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> saveVehicles(List<VehicleRegistry> items) async {
    await _prefs.setString(
      _vehiclesKey,
      jsonEncode(items.map((e) => e.toJson()).toList()),
    );
  }
}
