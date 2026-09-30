import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/CondominiumForm.vue');

test('carrega dados do condomínio para edição', () => {
  expect(content).toMatch('watch(() => props.condominium');
});
