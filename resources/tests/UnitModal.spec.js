import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/UnitModal.vue');

test('renderiza formulario da unidade', () => {
  expect(content).toMatch('N\u00famero da Unidade');
  expect(content).toMatch('Tipo *');
});
