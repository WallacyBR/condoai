import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/registry_models.dart';

/// Persistência local dos cadastros enquanto a API não está integrada.
class LocalRegistryStore {
  static const _residentsKey = 'registry_residents_v1';
  static const _petsKey = 'registry_pets_v1';
  static const _vehiclesKey = 'registry_vehicles_v1';
  static const _seededKey = 'registry_seeded_v1';

  final SharedPreferences _prefs;

  LocalRegistryStore(this._prefs);

  static Future<LocalRegistryStore> create() async {
    final prefs = await SharedPreferences.getInstance();
    final store = LocalRegistryStore(prefs);
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
        cpf: '412.***.***-89',
        rg: '12.345.678-9',
        cellPhone: '(11) 98765-4321',
        whatsapp: '(11) 98765-4321',
        address: 'Unidade 402 - Torre A',
        relationship: 'Titular / Proprietário',
        photoPath:
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
        isOwner: true,
      ),
      ResidentRegistry(
        id: 'seed-2',
        name: 'Larissa Silva',
        email: 'larissa@condoai.com',
        cpf: '389.***.***-20',
        cellPhone: '(11) 97654-3210',
        whatsapp: '(11) 97654-3210',
        address: 'Unidade 402 - Torre A',
        relationship: 'Cônjuge',
        photoPath:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&auto=format&fit=crop&q=80',
      ),
    ]);

    await savePets([
      PetRegistry(
        id: 'pet-1',
        name: 'Thor',
        species: 'Cão',
        breed: 'Golden Retriever',
        gender: 'Macho',
        age: '3 anos',
        weight: '32 kg',
        healthInfo: 'Vacinas V8/V10 e antirrábica em dia.',
        photoPath:
            'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&auto=format&fit=crop&q=80',
      ),
    ]);

    await saveVehicles([
      VehicleRegistry(
        id: 'veh-1',
        licensePlate: 'BRA-4X91',
        model: 'Jeep Compass Limited',
        color: 'Cinza Metálico',
        year: '2022',
        parkingSpot: 'Vaga G1 - 42 (Coberta)',
        notes: 'Tag LPR ativa na cancela de veículos.',
      ),
      VehicleRegistry(
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
