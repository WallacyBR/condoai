import '../models/occurrence_model.dart';
import 'tenant_scope.dart';

/// Repositório em memória de ocorrências, escopado por unidade (sem API ainda).
class OccurrenceLocalStore {
  static final Map<String, List<OccurrenceItem>> _byScope = {};

  static List<OccurrenceItem> load(TenantScope scope) {
    return List.unmodifiable(_byScope[scope.storageKey] ?? _defaultSeed());
  }

  static void add(TenantScope scope, OccurrenceItem item) {
    final list = List<OccurrenceItem>.from(_byScope[scope.storageKey] ?? _defaultSeed());
    list.insert(0, item);
    _byScope[scope.storageKey] = list;
  }

  static void replaceAll(TenantScope scope, List<OccurrenceItem> items) {
    _byScope[scope.storageKey] = List.from(items);
  }

  static List<OccurrenceItem> _defaultSeed() => [
        const OccurrenceItem(
          protocol: 'OC-9841',
          title: 'Lâmpada queimada no corredor do 4º andar',
          category: 'Manutenção Predial',
          date: 'Ontem às 18:20',
          status: 'Em Atendimento',
          description:
              'A lâmpada do corredor em frente ao apartamento 402 está piscando e apagou completamente.',
          location: 'Corredor do 4º andar — Bloco Primavera',
          urgency: 'Média',
        ),
      ];
}
