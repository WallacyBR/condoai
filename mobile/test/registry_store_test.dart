import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:condoai_app/data/local/local_registry_store.dart';
import 'package:condoai_app/data/models/registry_models.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('LocalRegistryStore', () {
    setUp(() {
      SharedPreferences.setMockInitialValues({});
    });

    test('persiste e recupera morador com campos completos', () async {
      final store = await LocalRegistryStore.create();
      final resident = ResidentRegistry(
        id: 'test-1',
        name: 'Maria Teste',
        cpf: '123.456.789-00',
        rg: '12.345.678-9',
        whatsapp: '(11) 98888-7777',
        address: 'Rua Teste, 100',
        personType: 'fisica',
        notes: 'Observação local',
      );

      await store.saveResidents([resident]);
      final loaded = await store.loadResidents();

      expect(loaded, hasLength(1));
      expect(loaded.first.name, 'Maria Teste');
      expect(loaded.first.whatsapp, '(11) 98888-7777');
      expect(loaded.first.address, 'Rua Teste, 100');
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
