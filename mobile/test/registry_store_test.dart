import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:predihub_app/data/local/local_registry_store.dart';
import 'package:predihub_app/data/local/tenant_scope.dart';
import 'package:predihub_app/data/models/registry_models.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('LocalRegistryStore', () {
    setUp(() {
      SharedPreferences.setMockInitialValues({});
    });

    test('persiste morador com campos completos incluindo endereço estruturado', () async {
      const scope = TenantScope(apartmentId: 101, condominiumName: 'Residencial Teste');
      final store = await LocalRegistryStore.create(scope: scope);
      final resident = ResidentRegistry(
        id: 'test-1',
        name: 'Maria Teste',
        cpf: '123.456.789-00',
        rg: '12.345.678-9',
        commercialPhone: '(11) 3333-4444',
        whatsapp: '(11) 98888-7777',
        billingAddressType: 'custom',
        cep: '01310-100',
        address: 'Av. Paulista, 1000',
        neighborhood: 'Bela Vista',
        city: 'São Paulo',
        state: 'SP',
        classification: 'morador',
        relationship: 'Cônjuge',
        personType: 'fisica',
        notes: 'Observação local',
      );

      await store.saveResidents([resident]);
      final loaded = await store.loadResidents();

      expect(loaded, hasLength(1));
      expect(loaded.first.commercialPhone, '(11) 3333-4444');
      expect(loaded.first.cep, '01310-100');
      expect(loaded.first.classification, 'morador');
      expect(loaded.first.relationship, 'Cônjuge');
    });

    test('chaves prefs são escopadas por unidade — dados não vazam entre tenants', () async {
      const scopeA = TenantScope(apartmentId: 101, condominiumName: 'Condo A');
      const scopeB = TenantScope(apartmentId: 205, condominiumName: 'Condo B');

      final storeA = await LocalRegistryStore.create(scope: scopeA);
      await storeA.saveResidents([
        const ResidentRegistry(id: 'only-a', name: 'Morador A', cpf: '111.111.111-11'),
      ]);

      final storeB = await LocalRegistryStore.create(scope: scopeB);
      final loadedB = await storeB.loadResidents();

      // scope B tem seed próprio, mas não deve conter morador exclusivo de A
      expect(loadedB.any((r) => r.id == 'only-a'), isFalse);
    });

    test('PetRegistry usa espécie customizada quando Outro', () {
      const pet = PetRegistry(
        id: 'p1',
        name: 'Bob',
        species: 'Outro',
        customSpecies: 'Iguana',
      );
      expect(pet.displaySpecies, 'Iguana');
    });
  });
}
