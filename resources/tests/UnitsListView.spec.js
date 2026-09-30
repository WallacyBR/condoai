import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/UnitsListView.vue');

test('exibe filtros principais', () => {
  expect(content).toMatch('Buscar unidades');
  expect(content).toMatch('Gerenciar Unidades');
});
