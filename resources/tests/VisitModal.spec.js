import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/VisitModal.vue');

test('renderiza modal de visita', () => {
  expect(content).toMatch('Nova Visita');
  expect(content).toMatch('Nome do Visitante');
});
